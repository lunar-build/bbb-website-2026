<?php

/**
 * Migrate the old site's news posts + media library into this site, swapping
 * the old theme's blocks for this theme's ACF blocks. See README.md alongside.
 *
 *   wp eval-file scripts/news-migration/migrate-news.php <mode> [ids]
 *
 * Modes:
 *   check        read-only: ID collisions, missing categories, unmapped blocks, bad ACF keys
 *   preview      read-only: write each transformed post to ./migration-preview/
 *   categories   create any source categories missing locally (matched on slug)
 *   attachments  copy every source attachment (+ meta), keeping its ID
 *   posts        copy published posts (optionally only comma-separated ids), keeping IDs;
 *                re-running replaces posts already imported
 *   case-studies copy published case studies (optionally only comma-separated ids), keeping IDs,
 *                quote/card_image fields and case_study_tag terms; run after `attachments`
 *   map-pages    copy the shops/BMX/pumps pages (+ parents) onto the Map Listing template, keeping
 *                IDs and URLs; featured image becomes an Image Hero; also rebuilds the bike pump
 *                fault form in Gravity Forms + its page; run after `attachments`
 *
 * Env:
 *   NEWS_SOURCE_DB        source database name (default prod_scratch)
 *   NEWS_SOURCE_DB_USER   / NEWS_SOURCE_DB_PASSWORD (default: this site's DB credentials)
 *   NEWS_SOURCE_PREFIX    source table prefix (default wp_)
 *   NEWS_SOURCE_HOME      old site URL to rewrite to this site's (default https://betterbybike.info)
 */

global $wpdb;

$mode = $args[0] ?? 'check';
$only = isset($args[1]) ? array_map('intval', explode(',', $args[1])) : null;

$src = new wpdb(
    getenv('NEWS_SOURCE_DB_USER') ?: DB_USER,
    getenv('NEWS_SOURCE_DB_PASSWORD') ?: DB_PASSWORD,
    getenv('NEWS_SOURCE_DB') ?: 'prod_scratch',
    DB_HOST,
);
$src->set_prefix(getenv('NEWS_SOURCE_PREFIX') ?: 'wp_');

$sourceHome = rtrim(getenv('NEWS_SOURCE_HOME') ?: 'https://betterbybike.info', '/');
$sourceHost = parse_url($sourceHome, PHP_URL_HOST);
$localHome = rtrim(home_url(), '/');

/*
|--------------------------------------------------------------------------
| Block building
|--------------------------------------------------------------------------
*/

/** The block class's default padding, so migrated blocks match freshly inserted ones. */
function news_block_spacing(string $class): array
{
    $spacing = (new ReflectionClass($class))->getDefaultProperties()['spacing'] ?? null;

    return ! empty($spacing['padding']) ? ['style' => ['spacing' => ['padding' => $spacing['padding']]]] : [];
}

/**
 * ACF block data stores every value next to its field key. Keys follow ACF
 * Composer's naming (field_<group>_<path>, repeater indexes dropped); pass
 * $keys for anything that doesn't, e.g. flexible-content layouts.
 */
function news_acf_block(string $name, string $class, array $fields, string $group, array $keys = []): array
{
    $data = [];
    foreach ($fields as $key => $value) {
        $data[$key] = $value;
        $path = preg_replace('/_\d+_/', '_', $key);
        $data['_' . $key] = $keys[$path] ?? 'field_' . $group . '_' . $path;
    }

    return [
        'blockName' => $name,
        'attrs' => ['name' => $name, 'data' => $data, 'mode' => 'preview'] + news_block_spacing($class),
        'innerBlocks' => [],
        'innerHTML' => '',
        'innerContent' => [],
    ];
}

function news_plain(string $html): string
{
    return trim(html_entity_decode(wp_strip_all_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
}

function news_is_blank(array $b): bool
{
    return $b['blockName'] === null && trim($b['innerHTML']) === '';
}

/*
|--------------------------------------------------------------------------
| Transforms: old block -> this theme's block(s)
|--------------------------------------------------------------------------
*/

function news_lead(array $b): array
{
    return news_acf_block('acf/standfirst', \App\Blocks\Standfirst::class, ['text' => news_plain($b['innerHTML'])], 'standfirst');
}

function news_quote(array $b): array
{
    $d = $b['attrs']['data'] ?? [];
    $quote = trim(str_replace("\r\n", "\n", (string) ($d['content'] ?? '')));

    return news_acf_block('acf/quote', \App\Blocks\Quote::class, [
        'quote' => $quote,
        // Short pull-quotes keep the large Figma "Short" scale; longer ones drop to standard.
        'quote_size' => mb_strlen($quote) > 200 ? 'standard' : 'large',
        'attribution_name' => trim((string) ($d['author'] ?? '')),
        'attribution_role' => trim((string) ($d['source'] ?? '')),
        'image' => $d['image'] ?? '',
    ], 'quote');
}

function news_notification(array $b): array
{
    $d = $b['attrs']['data'] ?? [];
    $text = esc_html(trim((string) ($d['content'] ?? '')));
    $link = $d['link'] ?? null;

    if (is_array($link) && ! empty($link['url'])) {
        $target = ! empty($link['target']) ? ' target="' . esc_attr($link['target']) . '" rel="noopener"' : '';
        $text .= ' <a href="' . esc_url($link['url']) . '"' . $target . '>' . esc_html($link['title'] ?: $link['url']) . '</a>';
    }

    return news_acf_block('acf/callout-text', \App\Blocks\CalloutText::class, ['text' => $text], 'callout_text');
}

/** Consecutive download-links merge into one Link Card with a card each. */
function news_downloads(array $blocks, wpdb $src): array
{
    $fields = ['cards' => count($blocks)];
    foreach ($blocks as $i => $b) {
        $id = (int) ($b['attrs']['data']['file'] ?? 0);
        $file = $src->get_var($src->prepare("SELECT meta_value FROM {$src->postmeta} WHERE post_id=%d AND meta_key='_wp_attached_file'", $id));
        $path = $file ? wp_upload_dir()['basedir'] . '/' . $file : null;
        $ext = $file ? strtoupper(pathinfo($file, PATHINFO_EXTENSION)) : '';
        $fields += [
            "cards_{$i}_logo" => '',
            "cards_{$i}_title" => (string) $src->get_var($src->prepare("SELECT post_title FROM {$src->posts} WHERE ID=%d", $id)),
            "cards_{$i}_description" => '',
            "cards_{$i}_cta_type" => 'download',
            "cards_{$i}_link" => '',
            "cards_{$i}_file" => $id,
            "cards_{$i}_cta_label" => 'Download',
            // Needs the uploads in place first; blank (label hidden) otherwise.
            "cards_{$i}_file_size" => $path && file_exists($path) ? trim($ext . ' ' . str_replace(' ', '', size_format(filesize($path), 1))) : '',
        ];
    }

    return news_acf_block('acf/link-card', \App\Blocks\LinkCard::class, $fields, 'link_card');
}

/** All bike-cards in a post become one Timeline + one Media Gallery of their images. */
function news_bike_cards(array $cards): array
{
    $steps = ['marker_size' => 'date', 'heading' => '', 'intro' => '', 'steps' => count($cards)];
    $gallery = ['items' => count($cards)];

    foreach ($cards as $i => $b) {
        $d = $b['attrs']['data'] ?? [];
        // "2018 - 2019 - Cycle Superheroes" -> label "2018–19", heading "Cycle Superheroes"
        preg_match('/^\s*(\d{4})(?:\s*-\s*(\d{4}))?\s*-\s*(.+)$/u', (string) ($d['title'] ?? ''), $m);
        $label = $m ? $m[1] . (! empty($m[2]) ? '–' . substr($m[2], 2) : '') : '';
        $heading = $m ? trim($m[3]) : trim((string) ($d['title'] ?? ''));

        $steps += [
            "steps_{$i}_label" => $label,
            "steps_{$i}_heading" => $heading,
            "steps_{$i}_body" => trim((string) ($d['text'] ?? '')),
        ];
        $gallery += [
            "items_{$i}_media_type" => 'image',
            "items_{$i}_image" => $d['bike_image'] ?? '',
            "items_{$i}_video" => '',
            "items_{$i}_video_poster" => '',
            "items_{$i}_alt" => $heading,
            "items_{$i}_video_alt" => '',
            "items_{$i}_column_span" => '1 column (square)',
        ];
    }

    return [
        news_acf_block('acf/timeline', \App\Blocks\Timeline::class, $steps, 'timeline'),
        news_acf_block('acf/media-gallery', \App\Blocks\MediaGallery::class, $gallery, 'media_gallery'),
    ];
}

function news_image(array $b): array
{
    preg_match('#<figcaption[^>]*>(.*?)</figcaption>#s', $b['innerHTML'], $m);

    return news_acf_block('acf/image-block', \App\Blocks\ImageBlock::class, [
        'image' => $b['attrs']['id'] ?? '',
        'content_type' => 'caption',
        'caption' => $m ? news_plain($m[1]) : '',
    ], 'image_block');
}

/** One core/buttons group -> one Button block per button. */
function news_buttons(array $b): array
{
    $out = [];
    foreach ($b['innerBlocks'] as $button) {
        if (! preg_match('#<a[^>]*href="([^"]*)"[^>]*>(.*?)</a>#s', $button['innerHTML'], $m)) {
            continue;
        }
        preg_match('#target="([^"]*)"#', $m[0], $t);
        $out[] = news_acf_block('acf/button', \App\Blocks\ButtonBlock::class, [
            'link' => ['title' => news_plain($m[2]), 'url' => html_entity_decode($m[1]), 'target' => $t[1] ?? ''],
            'button_style' => 'brand',
            'button_size' => 'large',
            'button_align' => 'left',
        ], 'button');
    }

    return $out;
}

/** Inline markup a Bullet List item keeps (see components/bullet-list.blade.php). */
const NEWS_LIST_INLINE = '<strong><b><em><i><a>';

/**
 * core/list -> Bullet List. Numbered lists stay numbered; nested lists keep
 * their depth via each item's indent level (Bullet List supports 0–2). Items
 * keep bold/italic/links.
 */
function news_list(array $b, array &$notes): array
{
    $items = [];
    $collect = function (array $list, int $level) use (&$collect, &$items, &$notes) {
        if ($level > 2) {
            $notes[] = 'list nested deeper than 3 levels — extra levels indented at 2';
        }
        foreach ($list['innerBlocks'] as $li) {
            // The item's own text is the HTML before any nested list.
            $own = preg_replace('#<(ul|ol)\b.*#s', '', $li['innerHTML']);
            $text = trim(html_entity_decode(strip_tags($own, NEWS_LIST_INLINE), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($text !== '') {
                $items[] = ['text' => $text, 'level' => min($level, 2)];
            }
            foreach ($li['innerBlocks'] as $sub) {
                $collect($sub, $level + 1);
            }
        }
    };
    $collect($b, 0);

    $fields = [
        'heading' => '',
        'list_style' => ! empty($b['attrs']['ordered']) ? 'numbers' : 'bullets',
        'items' => count($items),
    ];
    foreach ($items as $i => $item) {
        $fields["items_{$i}_text"] = $item['text'];
        $fields["items_{$i}_level"] = (string) $item['level'];
    }

    return news_acf_block('acf/bullet-list', \App\Blocks\BulletList::class, $fields, 'bullet_list');
}

/** core/table -> Table block; a first row that's all bold becomes the column headings. */
function news_table(array $b): array
{
    preg_match_all('#<tr[^>]*>(.*?)</tr>#s', $b['innerHTML'], $rows);
    $rows = array_map(function ($row) {
        preg_match_all('#<t[hd][^>]*>(.*?)</t[hd]>#s', $row, $cells);

        return $cells[1];
    }, $rows[1]);

    $first = $rows[0] ?? [];
    $isHeader = $first && ! array_filter($first, fn($c) => ! preg_match('#^\s*<(strong|b)>.*</\1>\s*$#s', $c) && trim($c) !== '');
    $headings = $isHeader ? array_map('news_plain', array_shift($rows)) : array_fill(0, count($first), '');

    preg_match('#<figcaption[^>]*>(.*?)</figcaption>#s', $b['innerHTML'], $caption);

    $fields = [
        'caption' => $caption ? news_plain($caption[1]) : '',
        'first_column_is_header' => 0,
        'columns' => count($headings),
    ];
    foreach ($headings as $i => $heading) {
        $fields["columns_{$i}_heading"] = $heading;
    }
    $fields['rows'] = count($rows);
    foreach ($rows as $r => $cells) {
        $fields["rows_{$r}_cells"] = count($cells);
        foreach ($cells as $c => $cell) {
            $fields["rows_{$r}_cells_{$c}_content"] = trim($cell);
        }
    }

    return news_acf_block('acf/data-table', \App\Blocks\Table::class, $fields, 'table');
}

/** A two-column core/columns holding one image each -> Two Column Content, image both sides. */
function news_image_columns(array $b): ?array
{
    $images = [];
    foreach ($b['innerBlocks'] as $column) {
        $inner = array_values(array_filter($column['innerBlocks'], fn($i) => ! news_is_blank($i)));
        if (count($inner) !== 1 || $inner[0]['blockName'] !== 'core/image') {
            return null;
        }
        $images[] = $inner[0]['attrs']['id'] ?? '';
    }
    if (count($images) !== 2) {
        return null;
    }

    return news_acf_block('acf/two-column-content', \App\Blocks\TwoColumnContent::class, [
        'left' => ['image'],
        'left_0_image' => $images[0],
        'right' => ['image'],
        'right_0_image' => $images[1],
    ], 'two_column_content', [
        'left_image' => 'field_two_column_content_left_image_image',
        'right_image' => 'field_two_column_content_right_image_image',
    ]);
}

/** Every block nested anywhere under $blocks, wrappers included. */
function news_flatten(array $blocks): array
{
    $all = [];
    foreach ($blocks as $b) {
        $all[] = $b;
        array_push($all, ...news_flatten($b['innerBlocks']));
    }

    return $all;
}

/**
 * A group/columns layout holding only old acf/card blocks (icon or image,
 * title, text, link) -> one Card Grid of "link" cards.
 */
function news_old_cards(array $b): ?array
{
    $leaves = array_filter(news_flatten($b['innerBlocks']), fn($i) => ! $i['innerBlocks'] && ! news_is_blank($i));
    if (! $leaves || array_filter($leaves, fn($i) => $i['blockName'] !== 'acf/card')) {
        return null;
    }

    $fields = ['card_style' => 'link', 'cards' => count($leaves)];
    foreach (array_values($leaves) as $n => $card) {
        $d = $card['attrs']['data'] ?? [];
        $fields += [
            "cards_{$n}_image" => $d['icon'] ?? $d['image'] ?? '',
            "cards_{$n}_heading_text" => $d['title'] ?? '',
            "cards_{$n}_heading_level" => 'h3',
            "cards_{$n}_heading_style" => 'match',
            "cards_{$n}_body_text" => $d['text'] ?? '',
            "cards_{$n}_body_style" => 'body',
            "cards_{$n}_link" => $d['link'] ?? '',
        ];
    }

    return news_acf_block('acf/card-grid', \App\Blocks\CardGrid::class, $fields, 'card_grid');
}

/** Swap each outermost all-acf/card group/columns, at any depth, 1:1 for a Card Grid. */
function news_replace_old_cards(array $blocks, array &$notes): array
{
    foreach ($blocks as &$b) {
        if (in_array($b['blockName'], ['core/group', 'core/columns'], true) && ($cards = news_old_cards($b))) {
            $b = $cards;
            $notes[] = 'acf/card layout -> Card Grid';
        } elseif ($b['innerBlocks']) {
            $b['innerBlocks'] = news_replace_old_cards($b['innerBlocks'], $notes);
        }
    }

    return $blocks;
}

/**
 * Inside layouts (groups, columns): core/image and core/list swap 1:1,
 * core/buttons expands to one Button block each — innerContent's null placeholders are rebuilt to
 * match so serialize_block() still lines blocks up with their wrapper markup.
 */
function news_nested(array $parent, array &$notes = []): array
{
    $blocks = [];
    $content = [];
    $i = 0;
    foreach ($parent['innerContent'] as $chunk) {
        if ($chunk !== null) {
            $content[] = $chunk;

            continue;
        }
        $b = $parent['innerBlocks'][$i++];
        $swapped = match ($b['blockName']) {
            'core/image' => [news_image($b)],
            'core/buttons' => news_buttons($b),
            'core/list' => [news_list($b, $notes)],
            default => [$b['innerBlocks'] ? news_nested($b, $notes) : $b],
        };
        foreach ($swapped as $block) {
            $blocks[] = $block;
            $content[] = null;
        }
    }

    return ['innerBlocks' => $blocks, 'innerContent' => $content] + $parent;
}

/**
 * A block straight after a heading belongs to it (e.g. "Posters" + its download
 * cards) — drop its default top padding so the heading's own margin is the gap.
 */
function news_tighten_after_headings(array $blocks): array
{
    $prev = null;
    foreach ($blocks as &$b) {
        if (news_is_blank($b)) {
            continue;
        }
        if ($prev === 'core/heading' && str_starts_with((string) $b['blockName'], 'acf/')) {
            unset($b['attrs']['style']['spacing']['padding']['top']);
        }
        $prev = $b['blockName'];
    }

    return $blocks;
}

function news_transform(string $content, wpdb $src, array &$notes): string
{
    // Old card layouts are spotted by their wrapping group, so swap those
    // first; then lift every block out of its group so the loop below
    // converts blocks at any depth, not just the top level.
    $in = news_unwrap_groups(news_replace_old_cards(parse_blocks($content), $notes));
    $out = [];
    $bikes = [];
    $bikeSlot = null;

    for ($i = 0; $i < count($in); $i++) {
        $b = $in[$i];
        switch ($b['blockName']) {
            case 'custom/lead':
                $out[] = news_lead($b);
                break;
            case 'acf/quote':
                $out[] = news_quote($b);
                break;
            case 'acf/notification':
                $out[] = news_notification($b);
                break;
            case 'core/image':
                $out[] = news_image($b);
                break;
            case 'core/buttons':
                array_push($out, ...news_buttons($b));
                break;
            case 'core/table':
                $out[] = news_table($b);
                break;
            case 'core/list':
                $out[] = news_list($b, $notes);
                break;
            case 'core/columns':
                $columns = news_image_columns($b);
                if (! $columns) {
                    $notes[] = 'core/columns kept as-is (not two single images)';
                }
                $out[] = $columns ?? news_nested($b, $notes);
                break;
            case 'acf/download-link':
                $run = [$b];
                // Skip the whitespace-only freeform blocks parse_blocks puts between siblings.
                while (isset($in[$i + 1]) && ($in[$i + 1]['blockName'] === 'acf/download-link' || (news_is_blank($in[$i + 1]) && ($in[$i + 2]['blockName'] ?? null) === 'acf/download-link'))) {
                    $i++;
                    if ($in[$i]['blockName']) {
                        $run[] = $in[$i];
                    }
                }
                $out[] = news_downloads($run, $src);
                break;
            case 'acf/bike-card':
                $bikes[] = $b;
                $bikeSlot ??= count($out);
                $out[] = null; // placeholder, filled once all cards are collected
                break;
            default:
                if ($b['blockName'] && ! str_starts_with($b['blockName'], 'core/')) {
                    $notes[] = 'UNMAPPED ' . $b['blockName'];
                }
                $out[] = $b['innerBlocks'] ? news_nested($b, $notes) : $b;
        }
    }

    if ($bikes) {
        $out = array_values(array_filter($out, fn($b) => $b !== null));
        array_splice($out, $bikeSlot, 0, news_bike_cards($bikes));
        $notes[] = count($bikes) . ' bike-cards -> Timeline + Media Gallery (blocks that were between them now follow)';
    }

    return serialize_blocks(news_tighten_after_headings($out));
}

/**
 * The old theme wrapped sections in core/group purely for its own style
 * classes (is-style-lightblue, is-style-block-fullwidth-bg), which mean
 * nothing here — lift their contents out (at any depth) so each block gets
 * converted and spaces itself. Columns stay; news_nested() handles those.
 */
function news_unwrap_groups(array $blocks): array
{
    $out = [];
    foreach ($blocks as $b) {
        if (($b['blockName'] ?? null) === 'core/group') {
            array_push($out, ...news_unwrap_groups($b['innerBlocks']));
        } else {
            $out[] = $b;
        }
    }

    return $out;
}

/** Every ACF field key the transforms wrote, that ACF doesn't know about. */
function news_bad_keys(string $content): array
{
    $bad = [];
    $walk = function ($blocks) use (&$walk, &$bad) {
        foreach ($blocks as $b) {
            foreach ($b['attrs']['data'] ?? [] as $k => $v) {
                if ($k[0] === '_' && is_string($v) && str_starts_with($v, 'field_') && ! acf_get_field($v)) {
                    $bad[$v] = true;
                }
            }
            $walk($b['innerBlocks']);
        }
    };
    $walk(parse_blocks($content));

    return array_keys($bad);
}

/*
|--------------------------------------------------------------------------
| Copying rows
|--------------------------------------------------------------------------
*/

$replaceUrls = fn(string $s) => str_replace(
    [$sourceHome, 'http://' . $sourceHost, 'https://www.' . $sourceHost, 'http://www.' . $sourceHost],
    $localHome,
    $s,
);

$postIds = $src->get_col("SELECT ID FROM {$src->posts} WHERE post_type='post' AND post_status='publish' ORDER BY ID");
if ($only) {
    $postIds = array_values(array_intersect($postIds, $only));
}
$attachmentIds = $src->get_col("SELECT ID FROM {$src->posts} WHERE post_type='attachment' ORDER BY ID");

$sourceCats = $src->get_results("SELECT t.term_id, t.name, t.slug, tt.parent FROM {$src->terms} t JOIN {$src->term_taxonomy} tt USING(term_id) WHERE tt.taxonomy='category' ORDER BY tt.parent, t.term_id");
$catMap = [];
foreach ($sourceCats as $t) {
    $local = get_term_by('slug', $t->slug, 'category');
    $catMap[$t->term_id] = $local ? $local->term_id : null;
}

$skipMeta = ['_edit_lock', '_edit_last', '_acf_changed', '_imagify_optimization_level', '_imagify_status', '_imagify_data'];

function news_copy_row(wpdb $src, int $id, callable $replaceUrls, array $overrides = []): void
{
    global $wpdb;
    $row = $src->get_row($src->prepare("SELECT * FROM {$src->posts} WHERE ID=%d", $id), ARRAY_A);
    $row['guid'] = $replaceUrls($row['guid']);
    // Source users aren't migrated.
    $row['post_author'] = 1;
    $wpdb->insert($wpdb->posts, array_merge($row, $overrides));
}

function news_copy_meta(wpdb $src, int $id, array $skip, callable $filter): void
{
    global $wpdb;
    foreach ($src->get_results($src->prepare("SELECT meta_key, meta_value FROM {$src->postmeta} WHERE post_id=%d", $id)) as $m) {
        if (in_array($m->meta_key, $skip, true) || str_starts_with($m->meta_key, '_oembed')) {
            continue;
        }
        $value = $filter($m->meta_key, $m->meta_value);
        if ($value !== null) {
            $wpdb->insert($wpdb->postmeta, ['post_id' => $id, 'meta_key' => $m->meta_key, 'meta_value' => $value]);
        }
    }
}

/**
 * Clear an earlier page import so it can be re-copied under the same ID.
 * Not wp_delete_post(): that also deletes nav menu items linking to the page.
 */
function news_remove_page_import(int $id): void
{
    global $wpdb;
    $wpdb->delete($wpdb->postmeta, ['post_id' => $id]);
    $wpdb->delete($wpdb->posts, ['ID' => $id]);
    clean_post_cache($id);
}

/**
 * Old shops/BMX/pumps template pages → Map Listing template, keeping IDs and
 * URLs. Old site ID => [dataset, preset services]; null = parent page.
 * Presets decoded from the old selected_categories field.
 */
const NEWS_MAP_PAGES = [
    705 => null,
    707 => null,
    715 => null,
    287 => ['shops', ['New bikes', 'Secondhand bikes', 'E-Bikes', 'Custom bikes']],
    288 => ['shops', ['Secondhand bikes']],
    289 => ['shops', ['Donate a bike']],
    295 => ['shops', ['Bike hire', 'Bike tours']],
    335 => ['shops', ['Bike servicing', 'Mobile bike repairs']],
    1121 => ['pumps', []],
    1292 => ['bmx', []],
];

/** Local rows already using any of $ids. */
function news_taken(array $ids): array
{
    global $wpdb;

    return $ids ? $wpdb->get_results('SELECT ID, post_type, post_title FROM ' . $wpdb->posts . ' WHERE ID IN (' . implode(',', array_map('intval', $ids)) . ')') : [];
}

/** Posts this script imported carry this flag, so a re-run may replace them — and only them. */
const NEWS_MIGRATED_META = '_news_migrated';

function news_post_clashes(array $ids): array
{
    return array_values(array_filter(news_taken($ids), fn($r) => ! ($r->post_type === 'post' && get_post_meta($r->ID, NEWS_MIGRATED_META, true))));
}

/*
|--------------------------------------------------------------------------
| Modes
|--------------------------------------------------------------------------
*/

if ($mode === 'check') {
    echo 'Source: ' . count($postIds) . ' published posts, ' . count($attachmentIds) . " attachments\n\n";

    // Attachments already copied, or posts this script imported, are expected.
    $taken = array_merge(
        news_post_clashes($postIds),
        array_filter(news_taken($attachmentIds), fn($r) => $r->post_type !== 'attachment'),
    );
    echo "ID collisions (must be removed or renumbered before importing):\n";
    foreach ($taken ?: [null] as $r) {
        echo $r ? "  {$r->ID} {$r->post_type} \"{$r->post_title}\"\n" : "  none\n";
    }

    $missing = array_filter($sourceCats, fn($t) => ! $catMap[$t->term_id]);
    echo "\nCategories missing locally (create with `categories` mode):\n  " . (implode("\n  ", array_map(fn($t) => $t->slug, $missing)) ?: 'none') . "\n";

    $notes = [];
    $bad = [];
    foreach ($postIds as $id) {
        $postNotes = [];
        $content = news_transform($src->get_var($src->prepare("SELECT post_content FROM {$src->posts} WHERE ID=%d", $id)), $src, $postNotes);
        $bad = array_merge($bad, news_bad_keys($content));
        foreach ($postNotes as $n) {
            $notes[] = "$id: $n";
        }
    }
    echo "\nTransform notes:\n  " . (implode("\n  ", array_unique($notes)) ?: 'none') . "\n";

    // Every core block still left after the transform, across everything this
    // script imports. Each should be checked against the theme's own blocks
    // (app/Blocks) — if one of ours does the job, add a conversion here.
    // Walks every nesting level, so blocks inside groups/columns count too.
    $core = [];
    $unregistered = [];
    $walk = function (array $blocks) use (&$walk, &$core, &$unregistered) {
        foreach ($blocks as $b) {
            $name = (string) $b['blockName'];
            if (str_starts_with($name, 'core/')) {
                $core[$name] = ($core[$name] ?? 0) + 1;
            } elseif ($name && ! WP_Block_Type_Registry::get_instance()->is_registered($name)) {
                $unregistered[$name] = ($unregistered[$name] ?? 0) + 1;
            }
            $walk($b['innerBlocks']);
        }
    };
    $caseStudyIds = $src->get_col("SELECT ID FROM {$src->posts} WHERE post_type='case_study' AND post_status='publish'");
    foreach ([...$postIds, ...$caseStudyIds, ...array_keys(array_filter(NEWS_MAP_PAGES))] as $id) {
        $ignored = [];
        $walk(parse_blocks(news_transform($src->get_var($src->prepare("SELECT post_content FROM {$src->posts} WHERE ID=%d", $id)), $src, $ignored)));
    }
    arsort($unregistered);
    echo "\nOld-theme blocks left at any depth (not registered here — need a conversion):\n";
    foreach ($unregistered ?: ['none' => ''] as $name => $count) {
        echo "  $name $count\n";
    }
    arsort($core);
    echo "\nCore blocks left after transform (check each against app/Blocks for a replacement):\n";
    foreach ($core ?: ['none' => ''] as $name => $count) {
        echo "  $name $count\n";
    }
    echo "\nUnknown ACF field keys:\n  " . (implode("\n  ", array_unique($bad)) ?: 'none') . "\n";

    return;
}

if ($mode === 'preview') {
    $dir = getcwd() . '/migration-preview';
    wp_mkdir_p($dir);
    foreach ($postIds as $id) {
        $post = $src->get_row($src->prepare("SELECT post_name, post_content FROM {$src->posts} WHERE ID=%d", $id));
        $notes = [];
        file_put_contents("$dir/$id-{$post->post_name}.html", $replaceUrls(news_transform($post->post_content, $src, $notes)));
    }
    WP_CLI::success(count($postIds) . " posts written to $dir");

    return;
}

if ($mode === 'categories') {
    // Ordered parents-first, so a child's parent already exists when it's created.
    foreach ($sourceCats as $t) {
        if ($catMap[$t->term_id]) {
            continue;
        }
        $parent = $t->parent ? ($catMap[$t->parent] ?? 0) : 0;
        $created = wp_insert_term(html_entity_decode($t->name), 'category', ['slug' => $t->slug, 'parent' => $parent]);
        if (is_wp_error($created)) {
            WP_CLI::warning("{$t->slug}: " . $created->get_error_message());

            continue;
        }
        $catMap[$t->term_id] = $created['term_id'];
        echo "created {$t->slug}\n";
    }
    WP_CLI::success('Categories in place.');

    return;
}

if ($mode === 'attachments') {
    $existing = array_map(fn($r) => (string) $r->ID, array_filter(news_taken($attachmentIds), fn($r) => $r->post_type === 'attachment'));
    if ($clash = array_filter(news_taken($attachmentIds), fn($r) => $r->post_type !== 'attachment')) {
        WP_CLI::error('Local IDs already taken, resolve first (see `check`): ' . implode(',', array_map(fn($r) => $r->ID, $clash)));
    }
    $count = 0;
    foreach (array_diff($attachmentIds, $existing) as $id) {
        $parent = (int) $src->get_var($src->prepare("SELECT post_parent FROM {$src->posts} WHERE ID=%d", $id));
        // Keep the parent link only for posts we're also migrating.
        news_copy_row($src, $id, $replaceUrls, ['post_parent' => in_array((string) $parent, $postIds, true) ? $parent : 0]);
        news_copy_meta($src, $id, $skipMeta, fn($k, $v) => $v);
        clean_post_cache($id);
        $count++;
    }
    WP_CLI::success("$count attachments imported (" . count($existing) . ' already present).');

    return;
}

if ($mode === 'posts') {
    if ($clash = news_post_clashes($postIds)) {
        WP_CLI::error('Local IDs already taken, resolve first (see `check`): ' . implode(',', array_map(fn($r) => $r->ID, $clash)));
    }
    foreach ($postIds as $id) {
        // Re-running replaces posts imported earlier (same source IDs).
        if (get_post_meta($id, NEWS_MIGRATED_META, true)) {
            wp_delete_post($id, true);
        }
        $notes = [];
        $content = $src->get_var($src->prepare("SELECT post_content FROM {$src->posts} WHERE ID=%d", $id));
        news_copy_row($src, $id, $replaceUrls, [
            'post_content' => $replaceUrls(news_transform($content, $src, $notes)),
        ]);
        news_copy_meta($src, $id, $skipMeta, function ($k, $v) use ($catMap) {
            if ($k === '_yoast_wpseo_primary_category') {
                return isset($catMap[(int) $v]) ? (string) $catMap[(int) $v] : null;
            }

            return $v;
        });
        update_post_meta($id, NEWS_MIGRATED_META, 1);
        $terms = $src->get_col($src->prepare("SELECT tt.term_id FROM {$src->term_relationships} r JOIN {$src->term_taxonomy} tt USING(term_taxonomy_id) WHERE r.object_id=%d AND tt.taxonomy='category'", $id));
        wp_set_post_terms($id, array_values(array_filter(array_map(fn($t) => $catMap[$t] ?? null, $terms))), 'category');
        clean_post_cache($id);
        echo "imported $id\n";
    }
    WP_CLI::success(count($postIds) . ' posts imported.');

    return;
}

if ($mode === 'case-studies') {
    $ids = $src->get_col("SELECT ID FROM {$src->posts} WHERE post_type='case_study' AND post_status='publish' ORDER BY ID");
    if ($only) {
        $ids = array_values(array_intersect($ids, $only));
    }
    $clash = array_filter(news_taken($ids), fn($r) => ! ($r->post_type === 'case_study' && get_post_meta($r->ID, NEWS_MIGRATED_META, true)));
    if ($clash) {
        WP_CLI::error('Local IDs already taken, resolve first: ' . implode(',', array_map(fn($r) => $r->ID, $clash)));
    }

    // Only fields App\Fields\CaseStudy keeps; the old illustration fields belonged to the old theme's layout.
    $fieldKeys = ['quote' => 'field_case_study_quote', 'card_image' => 'field_case_study_card_image'];
    $keepMeta = ['_thumbnail_id', '_wp_old_slug', ...array_keys($fieldKeys)];

    foreach ($ids as $id) {
        if (get_post_meta($id, NEWS_MIGRATED_META, true)) {
            wp_delete_post($id, true);
        }
        $notes = [];
        $row = $src->get_row($src->prepare("SELECT post_content, post_excerpt FROM {$src->posts} WHERE ID=%d", $id));
        $quote = $src->get_var($src->prepare("SELECT meta_value FROM {$src->postmeta} WHERE post_id=%d AND meta_key='quote'", $id));
        news_copy_row($src, $id, $replaceUrls, [
            'post_content' => $replaceUrls(news_transform($row->post_content, $src, $notes)),
            // No excerpts on the old site — the quote is the case study's summary on archive cards.
            'post_excerpt' => $row->post_excerpt ?: (string) $quote,
        ]);
        news_copy_meta($src, $id, $skipMeta, fn($k, $v) => in_array($k, $keepMeta, true) || str_starts_with($k, '_yoast_wpseo_') ? $v : null);
        foreach ($fieldKeys as $name => $key) {
            update_post_meta($id, "_$name", $key);
        }
        update_post_meta($id, NEWS_MIGRATED_META, 1);

        $tags = $src->get_results($src->prepare("SELECT t.name, t.slug FROM {$src->term_relationships} r JOIN {$src->term_taxonomy} tt USING(term_taxonomy_id) JOIN {$src->terms} t USING(term_id) WHERE r.object_id=%d AND tt.taxonomy='case_study_tag'", $id));
        foreach ($tags as $t) {
            if (! term_exists($t->slug, 'case_study_tag')) {
                wp_insert_term(html_entity_decode($t->name), 'case_study_tag', ['slug' => $t->slug]);
            }
        }
        wp_set_object_terms($id, array_map(fn($t) => $t->slug, $tags), 'case_study_tag');
        clean_post_cache($id);
        echo "imported $id\n";
    }
    WP_CLI::success(count($ids) . ' case studies imported.');

    return;
}

/**
 * Gravity Forms copy of the old Contact Form 7 "Public Bike Pumps - Report a
 * fault" form (old ID 4444): same fields, emailed to the same inbox. Matched
 * on title, so re-running reuses it rather than adding another.
 */
function news_pump_fault_form(): int
{
    $title = 'Public Bike Pumps - Report a fault';
    foreach (GFAPI::get_forms() as $form) {
        if ($form['title'] === $title) {
            return (int) $form['id'];
        }
    }

    $field = fn(int $id, string $type, string $label, array $extra = []) => $extra + [
        'id' => $id,
        'type' => $type,
        'label' => $label,
        'isRequired' => true,
        'size' => 'large',
    ];

    $id = GFAPI::add_form([
        'title' => $title,
        'description' => '',
        'labelPlacement' => 'top_label',
        'descriptionPlacement' => 'above',
        'requiredIndicator' => 'text',
        'validationSummary' => '1',
        'markupVersion' => 2,
        'button' => ['type' => 'text', 'text' => 'Send'],
        'fields' => [
            $field(1, 'text', 'Your name', ['autocompleteAttribute' => 'name', 'enableAutocomplete' => true]),
            $field(2, 'email', 'Your email', ['autocompleteAttribute' => 'email', 'enableAutocomplete' => true]),
            $field(3, 'text', 'Bike pump location'),
            $field(4, 'textarea', 'Your message'),
        ],
        'notifications' => [
            'pump_fault' => [
                'id' => 'pump_fault',
                'name' => 'Report to Better by Bike',
                'event' => 'form_submission',
                'toType' => 'email',
                'to' => 'betterbybike@bristol.gov.uk',
                'from' => 'website@betterbybike.info',
                'fromName' => 'Better by Bike',
                'replyTo' => '{Your email:2}',
                'subject' => 'Public Bike Pumps enquiry',
                'message' => "From: {Your name:1}\nEmail: {Your email:2}\n\nLocation:\n{Bike pump location:3}\n\nMessage:\n{Your message:4}",
                'isActive' => true,
            ],
        ],
        'confirmations' => [
            'default' => [
                'id' => 'default',
                'name' => 'Default Confirmation',
                'isDefault' => true,
                'type' => 'message',
                'message' => 'Thank you for your message. It has been sent.',
            ],
        ],
    ]);
    if (is_wp_error($id)) {
        WP_CLI::error('Gravity Forms: ' . $id->get_error_message());
    }

    return (int) $id;
}

/**
 * The old page template drew its header from the featured image; here that's
 * an Image Hero block, or a Text Hero when there's no image (e.g. BMX), so the
 * title still gets a proper hero.
 */
function news_page_hero(string $title, int $thumb): array
{
    $title = html_entity_decode($title, ENT_QUOTES);
    $hero = $thumb
        ? news_acf_block('acf/image-hero', \App\Blocks\ImageHero::class, [
            'background_image' => $thumb,
            'image_position' => 'center',
            'show_heading' => '1',
            'heading' => $title,
            'show_breadcrumbs' => '1',
        ], 'image_hero')
        : news_acf_block('acf/text-hero', \App\Blocks\TextHero::class, [
            'heading_text' => $title,
            'heading_level' => 'h1',
            'heading_style' => 'match',
            'show_breadcrumbs' => '1',
        ], 'text_hero');
    $hero['attrs']['align'] = 'full';

    return $hero;
}

if ($mode === 'map-pages') {
    $pages = NEWS_MAP_PAGES;
    $clash = array_filter(news_taken([...array_keys($pages), 6669]), fn($r) => ! ($r->post_type === 'page' && get_post_meta($r->ID, NEWS_MIGRATED_META, true)));
    if ($clash) {
        WP_CLI::error('Local IDs already taken, resolve first: ' . implode(',', array_map(fn($r) => "{$r->ID} {$r->post_type}", $clash)));
    }

    foreach ($pages as $id => $config) {
        if (get_post_meta($id, NEWS_MIGRATED_META, true)) {
            news_remove_page_import($id);
        }
        $row = $src->get_row($src->prepare("SELECT post_title, post_content FROM {$src->posts} WHERE ID=%d", $id));
        $thumb = (int) $src->get_var($src->prepare("SELECT meta_value FROM {$src->postmeta} WHERE post_id=%d AND meta_key='_thumbnail_id'", $id));
        $notes = [];

        // Parents only held the old Child Page Cards block, which has no equivalent yet.
        $content = '';
        if ($config) {
            $content = news_transform($row->post_content, $src, $notes);
            // The intro sits in the archive column, already spaced by its grid — no Standfirst padding.
            $content = serialize_blocks(array_map(function ($b) {
                if ($b['blockName'] === 'acf/standfirst') {
                    $b['attrs']['style']['spacing']['padding'] = ['top' => '0', 'bottom' => '0'];
                }

                return $b;
            }, parse_blocks($content)));
            $content = serialize_block(news_page_hero($row->post_title, $thumb)) . "\n\n" . $content;
        }

        news_copy_row($src, $id, $replaceUrls, ['post_content' => $replaceUrls($content)]);
        news_copy_meta($src, $id, $skipMeta, fn($k, $v) => $k === '_thumbnail_id' || str_starts_with($k, '_yoast_wpseo_') ? $v : null);
        update_post_meta($id, NEWS_MIGRATED_META, 1);

        if ($config) {
            [$dataset, $services] = $config;
            update_post_meta($id, '_wp_page_template', 'template-map-listing.blade.php');
            update_field('map_listing_dataset', $dataset, $id);
            update_field('map_listing_services', $services, $id);
        }
        clean_post_cache($id);
        echo "imported $id " . ($notes ? '(' . implode('; ', $notes) . ')' : '') . "\n";
    }
    // The pumps page's "report a fault" button leads here: the old CF7 form, rebuilt in Gravity Forms.
    $faultId = 6669;
    if (get_post_meta($faultId, NEWS_MIGRATED_META, true)) {
        news_remove_page_import($faultId);
    }
    $title = $src->get_var($src->prepare("SELECT post_title FROM {$src->posts} WHERE ID=%d", $faultId));
    $hero = news_acf_block('acf/text-hero', \App\Blocks\TextHero::class, [
        'heading_text' => html_entity_decode($title, ENT_QUOTES),
        'heading_level' => 'h1',
        'heading_style' => 'match',
        'show_breadcrumbs' => '1',
    ], 'text_hero');
    $hero['attrs']['align'] = 'full';
    $form = news_acf_block('acf/form', \App\Blocks\Form::class, [
        'layout' => 'centred',
        'heading_text' => 'Tell us which pump and what\'s wrong',
        'heading_level' => 'h2',
        'heading_style' => 'match',
        'intro_text' => 'Public bike pumps are maintained by local authorities in the West of England. Let us know where the pump is and what the problem is.',
        'intro_style' => 'body',
        'form_id' => news_pump_fault_form(),
    ], 'form');
    news_copy_row($src, $faultId, $replaceUrls, ['post_content' => serialize_blocks([$hero, $form])]);
    news_copy_meta($src, $faultId, $skipMeta, fn($k, $v) => str_starts_with($k, '_yoast_wpseo_') ? $v : null);
    update_post_meta($faultId, NEWS_MIGRATED_META, 1);
    clean_post_cache($faultId);
    echo "imported $faultId (Gravity Form " . news_pump_fault_form() . ")\n";

    WP_CLI::success(count($pages) + 1 . ' pages imported.');

    return;
}

/*
|--------------------------------------------------------------------------
| Bristol Cycling Centre sessions -> Modern Events Calendar
|--------------------------------------------------------------------------
*/

/** Old cycling_centre_event posts that only filled gaps in the timetable grid. */
const CC_TIMETABLE_FILLERS = [800, 804, 2097];

/** Cycling centres > Bristol Cycling Centre > What's on, copied with their IDs and URLs. */
const CC_PAGES = [308, 309, 796];

/** Session dot colours from the Figma accordion timetable, handed out in turn (MEC stores them without the #). */
const CC_COLORS = ['ffbf47', 'f77ba0', '67dd00', '76dbe8', '1eacdb'];

/**
 * The old timetable lived in an ACF options repeater: one row per term
 * (start/end date, title), each with weekday rows of start/end/event ID.
 * Returns the terms keyed by their repeater index, skipping ended ones
 * unless $includeEnded.
 */
function cc_terms(wpdb $src, bool $includeEnded = false): array
{
    $opts = $src->get_results("SELECT option_name, option_value FROM {$src->options} WHERE option_name LIKE 'options\_schedule\_%'", OBJECT_K);
    $get = fn(string $key) => isset($opts["options_schedule_$key"]) ? $opts["options_schedule_$key"]->option_value : '';
    $today = current_time('Ymd');
    $terms = [];

    for ($t = 0; $get("{$t}_start_date") !== ''; $t++) {
        if (! $includeEnded && $get("{$t}_end_date") < $today) {
            continue;
        }
        $slots = [];
        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
            for ($i = 0; $i < (int) $get("{$t}_sessions_$day"); $i++) {
                $slots[] = [
                    'day' => $day,
                    'start' => $get("{$t}_sessions_{$day}_{$i}_start"),
                    'end' => $get("{$t}_sessions_{$day}_{$i}_end"),
                    'event' => (int) $get("{$t}_sessions_{$day}_{$i}_event"),
                ];
            }
        }
        $terms[$t] = [
            'title' => trim(preg_replace('/\s+/', ' ', $get("{$t}_schedule_title"))),
            'start' => $get("{$t}_start_date"),
            'end' => $get("{$t}_end_date"),
            'slots' => $slots,
        ];
    }

    return $terms;
}

/** Each term's weekday slots expanded into dated sessions, keyed by old event ID and sorted. */
function cc_dated_sessions(array $terms): array
{
    $sessions = [];
    foreach ($terms as $term) {
        $last = DateTimeImmutable::createFromFormat('Ymd', $term['end']);
        for ($date = DateTimeImmutable::createFromFormat('Ymd', $term['start']); $date <= $last; $date = $date->modify('+1 day')) {
            foreach ($term['slots'] as $slot) {
                if ($slot['day'] === strtolower($date->format('l'))) {
                    $sessions[$slot['event']][] = ['date' => $date->format('Y-m-d'), 'start' => $slot['start'], 'end' => $slot['end']];
                }
            }
        }
    }
    foreach ($sessions as &$list) {
        usort($list, fn($a, $b) => strcmp($a['date'] . $a['start'], $b['date'] . $b['start']));
    }

    return $sessions;
}

/** '13:15:00' -> ['01', '15', 'PM'], the 12-hour parts MEC stores times as. */
function cc_time_parts(string $time): array
{
    $t = DateTimeImmutable::createFromFormat('H:i:s', $time);

    return [$t->format('h'), $t->format('i'), $t->format('A')];
}

function cc_term(string $taxonomy, string $name, string $slug, array $meta = []): int
{
    $term = get_term_by('slug', $slug, $taxonomy) ?: (object) wp_insert_term($name, $taxonomy, ['slug' => $slug]);
    foreach ($meta as $key => $value) {
        update_term_meta($term->term_id, $key, $value);
    }

    return (int) $term->term_id;
}

/**
 * Drop old session-page blocks with no place on an MEC event page, at any
 * depth. innerContent's null placeholders are dropped alongside, so
 * serialize_block() still lines blocks up with their wrapper markup.
 */
function cc_strip_blocks(array $blocks, array &$notes): array
{
    $out = [];
    foreach ($blocks as $b) {
        $name = (string) $b['blockName'];
        // Next session + price now come from the single event template; the
        // old theme's decorative illustrations have no place in this one.
        if (in_array($name, ['acf/upcoming-sessions', 'acf/illustration'], true)) {
            continue;
        }
        // The old CF7 "Bristol Cycling Centre Contact" form needs rebuilding in Gravity Forms.
        if (in_array($name, ['acf/form', 'contact-form-7/contact-form-selector'], true)) {
            $notes[] = "dropped $name (CF7 contact form, rebuild in Gravity Forms)";

            continue;
        }
        if ($name === 'core-embed/youtube') {
            $b['blockName'] = 'core/embed';
        }
        if ($b['innerBlocks']) {
            $kept = [];
            $content = [];
            $i = 0;
            foreach ($b['innerContent'] as $chunk) {
                if ($chunk !== null) {
                    $content[] = $chunk;
                } elseif ($inner = cc_strip_blocks([$b['innerBlocks'][$i++]], $notes)) {
                    $kept[] = $inner[0];
                    $content[] = null;
                }
            }
            $b['innerBlocks'] = $kept;
            $b['innerContent'] = $content;
        }
        $out[] = $b;
    }

    return $out;
}

/** acf/image-slider -> Media Gallery of the same images. */
function cc_image_slider(array $b): array
{
    $d = $b['attrs']['data'] ?? [];
    $fields = ['items' => (int) ($d['slides'] ?? 0)];
    for ($i = 0; $i < $fields['items']; $i++) {
        $fields += [
            "items_{$i}_media_type" => 'image',
            "items_{$i}_image" => $d["slides_{$i}_image"] ?? '',
            "items_{$i}_video" => '',
            "items_{$i}_video_poster" => '',
            "items_{$i}_alt" => $d["slides_{$i}_image_caption"] ?? '',
            "items_{$i}_video_alt" => '',
            "items_{$i}_column_span" => '1 column (square)',
        ];
    }

    return news_acf_block('acf/media-gallery', \App\Blocks\MediaGallery::class, $fields, 'media_gallery');
}

if ($mode === 'cycling-centre') {
    if (! class_exists('MEC')) {
        WP_CLI::error('Modern Events Calendar must be active.');
    }
    $mec = MEC::getInstance('app.libraries.main');

    $ids = array_values(array_diff(
        $src->get_col("SELECT ID FROM {$src->posts} WHERE post_type='cycling_centre_event' AND post_status='publish' ORDER BY ID"),
        CC_TIMETABLE_FILLERS,
    ));
    if ($only) {
        $ids = array_values(array_intersect($ids, $only));
    }
    $clash = array_filter(news_taken([...$ids, ...CC_PAGES]), fn($r) => ! (in_array($r->post_type, ['mec-events', 'page'], true) && get_post_meta($r->ID, NEWS_MIGRATED_META, true)));
    if ($clash) {
        WP_CLI::error('Local IDs already taken, resolve first: ' . implode(',', array_map(fn($r) => "{$r->ID} {$r->post_type}", $clash)));
    }

    $category = cc_term('mec_category', 'Bristol Cycling Centre', 'bristol-cycling-centre');
    // The old site's one-off event categories, ready for new events.
    foreach (['cycle-training' => 'Cycle training', 'free-bike-check' => 'Free bike check', 'mass-participation' => 'Mass participation', 'roadshow-event' => 'Roadshow event'] as $slug => $name) {
        cc_term('mec_category', $name, $slug);
    }
    $location = cc_term('mec_location', 'Bristol Cycling Centre', 'bristol-cycling-centre', [
        'address' => 'Old Whitchurch Athletics Track, Bamfield, Bristol BS14 0XA',
    ]);

    $terms = cc_terms($src);
    $sessions = cc_dated_sessions($terms);

    foreach ($ids as $n => $id) {
        if (get_post_meta($id, NEWS_MIGRATED_META, true)) {
            wp_delete_post($id, true);
        }
        $row = $src->get_row($src->prepare("SELECT post_name, post_date, post_date_gmt, post_title, post_content FROM {$src->posts} WHERE ID=%d", $id));
        $notes = [];
        $content = serialize_blocks(cc_strip_blocks(parse_blocks($row->post_content), $notes));
        $content = serialize_blocks(array_map(
            fn($b) => $b['blockName'] === 'acf/image-slider' ? cc_image_slider($b) : $b,
            parse_blocks(news_transform($content, $src, $notes)),
        ));
        $notes = array_filter($notes, fn($n) => $n !== 'UNMAPPED acf/image-slider');

        // Reserve the old ID; save_event() then fills it in as an MEC event.
        news_copy_row($src, $id, $replaceUrls, ['post_type' => 'mec-events', 'post_content' => '']);
        clean_post_cache($id);

        $dates = $sessions[$id] ?? [];
        $first = $dates ? array_shift($dates) : null;
        // No sessions this term: a dateless one-off on its old publish date, so the page still exists.
        $start = $first['date'] ?? substr($row->post_date, 0, 10);
        [$sh, $sm, $sa] = cc_time_parts($first['start'] ?? '00:00:00');
        [$eh, $em, $ea] = cc_time_parts($first['end'] ?? '00:00:00');

        // MEC's custom-days format: Y-m-d:Y-m-d:hh-mm-AM:hh-mm-PM, comma-separated.
        $days = implode(',', array_map(function ($d) {
            return $d['date'] . ':' . $d['date'] . ':' . implode('-', cc_time_parts($d['start'])) . ':' . implode('-', cc_time_parts($d['end']));
        }, $dates));

        $price = (string) $src->get_var($src->prepare("SELECT meta_value FROM {$src->postmeta} WHERE post_id=%d AND meta_key='price'", $id));

        $saved = $mec->save_event([
            'title' => html_entity_decode($row->post_title, ENT_QUOTES),
            // wp_insert_post() unslashes, which would eat the \u003c escapes in block JSON.
            'content' => wp_slash($replaceUrls($content)),
            'status' => 'publish',
            'author' => 1,
            'start' => $start,
            'end' => $start,
            'start_time_hour' => $sh,
            'start_time_minutes' => $sm,
            'start_time_ampm' => $sa,
            'end_time_hour' => $eh,
            'end_time_minutes' => $em,
            'end_time_ampm' => $ea,
            'allday' => 0,
            'date' => ['hide_time' => $first ? 0 : 1],
            'repeat_status' => $days ? 1 : 0,
            'repeat_type' => $days ? 'custom_days' : '',
            'interval' => null,
            'days' => $days,
            // As MEC's own editor saves custom-days events: the repeat "ends" with the first date.
            'finish' => $days ? $start : '',
            'location_id' => $location,
            'meta' => [
                'mec_in_days' => $days,
                'mec_cost' => $price,
                'mec_color' => CC_COLORS[$n % count(CC_COLORS)],
            ],
        ], $id);
        if (is_wp_error($saved)) {
            WP_CLI::warning("$id: " . $saved->get_error_message());

            continue;
        }

        // wp_insert_post() regenerates the slug and dates from the title; put the old ones back.
        $wpdb->update($wpdb->posts, [
            'post_name' => $row->post_name,
            'post_date' => $row->post_date,
            'post_date_gmt' => $row->post_date_gmt,
        ], ['ID' => $id]);
        news_copy_meta($src, $id, $skipMeta, fn($k, $v) => in_array($k, ['_thumbnail_id', '_wp_old_slug'], true) || str_starts_with($k, '_yoast_wpseo_') ? $v : null);
        wp_set_object_terms($id, [$category], 'mec_category');
        wp_set_object_terms($id, [$location], 'mec_location');
        update_post_meta($id, NEWS_MIGRATED_META, 1);
        clean_post_cache($id);

        echo "imported $id {$row->post_name}: " . ($first ? count($dates) + 1 : 0) . ' sessions' . ($notes ? ' (' . implode('; ', array_unique($notes)) . ')' : '') . "\n";
    }

    // The pages above the sessions. What's on's old acf/calendar blocks (one
    // per term) become Events Timetable blocks covering the same term.
    $allTerms = cc_terms($src, true);
    foreach (CC_PAGES as $id) {
        if (get_post_meta($id, NEWS_MIGRATED_META, true)) {
            news_remove_page_import($id);
        }
        $row = $src->get_row($src->prepare("SELECT post_title, post_content FROM {$src->posts} WHERE ID=%d", $id));
        $thumb = (int) $src->get_var($src->prepare("SELECT meta_value FROM {$src->postmeta} WHERE post_id=%d AND meta_key='_thumbnail_id'", $id));
        $notes = [];
        $blocks = array_map(function ($b) use ($allTerms, $category, &$notes) {
            if ($b['blockName'] !== 'acf/calendar') {
                return $b;
            }
            $term = $allTerms[(int) ($b['attrs']['data']['schedule_choice'] ?? 0)] ?? null;
            $notes[] = 'acf/calendar -> Events Timetable' . ($term ? '' : ' (term not found, left empty)');

            return news_acf_block('acf/events-timetable', \App\Blocks\EventsTimetable::class, [
                'layout' => 'tabs',
                'category' => $category,
                'periods' => $term ? 1 : 0,
            ] + ($term ? [
                'periods_0_title' => $term['title'],
                'periods_0_start_date' => $term['start'],
                'periods_0_end_date' => $term['end'],
            ] : []), 'events_timetable');
        }, parse_blocks(news_transform(serialize_blocks(cc_strip_blocks(parse_blocks($row->post_content), $notes)), $src, $notes)));
        $notes = array_filter($notes, fn($n) => $n !== 'UNMAPPED acf/calendar');

        // What's on uses the Sticky Nav template (Figma 14:800: page index beside the
        // content), which supplies its own Text Hero and breadcrumbs, so no hero block.
        $stickyNav = $id === 796;
        $content = ($stickyNav ? '' : serialize_block(news_page_hero($row->post_title, $thumb)) . "\n\n") . serialize_blocks($blocks);
        news_copy_row($src, $id, $replaceUrls, ['post_content' => $replaceUrls($content)]);
        news_copy_meta($src, $id, $skipMeta, fn($k, $v) => $k === '_thumbnail_id' || str_starts_with($k, '_yoast_wpseo_') ? $v : null);
        update_post_meta($id, NEWS_MIGRATED_META, 1);
        if ($stickyNav) {
            update_post_meta($id, '_wp_page_template', 'template-sticky-nav.blade.php');
        }
        clean_post_cache($id);
        echo "imported page $id " . ($notes ? '(' . implode('; ', array_unique($notes)) . ')' : '') . "\n";
    }

    WP_CLI::success(count($ids) . ' Cycling Centre sessions and ' . count(CC_PAGES) . ' pages imported.');

    return;
}

WP_CLI::error("Unknown mode: $mode");
