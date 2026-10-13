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
 *                IDs and URLs; featured image becomes an Image Hero; run after `attachments`
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

/** core/image nested in other layouts swaps 1:1, so innerContent placeholders stay valid. */
function news_nested_images(array $blocks): array
{
    foreach ($blocks as &$b) {
        if ($b['blockName'] === 'core/image') {
            $b = news_image($b);
        } elseif ($b['innerBlocks']) {
            $b['innerBlocks'] = news_nested_images($b['innerBlocks']);
        }
    }

    return $blocks;
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
    $in = parse_blocks($content);
    $out = [];
    $bikes = [];
    $bikeSlot = null;

    for ($i = 0; $i < count($in); $i++) {
        $b = news_replace_old_cards([$in[$i]], $notes)[0];
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
            case 'core/columns':
                $columns = news_image_columns($b);
                if (! $columns) {
                    $notes[] = 'core/columns kept as-is (not two single images)';
                }
                $out[] = $columns ?? ['innerBlocks' => news_nested_images($b['innerBlocks'])] + $b;
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
                $out[] = $b['innerBlocks'] ? ['innerBlocks' => news_nested_images($b['innerBlocks'])] + $b : $b;
        }
    }

    if ($bikes) {
        $out = array_values(array_filter($out, fn($b) => $b !== null));
        array_splice($out, $bikeSlot, 0, news_bike_cards($bikes));
        $notes[] = count($bikes) . ' bike-cards -> Timeline + Media Gallery (blocks that were between them now follow)';
    }

    return serialize_blocks(news_tighten_after_headings($out));
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

if ($mode === 'map-pages') {
    // Old shops/BMX/pumps template pages → Map Listing template, keeping IDs
    // and URLs. Old site ID => [dataset, preset services]; null = parent page.
    // Presets decoded from the old selected_categories field.
    $pages = [
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
    $clash = array_filter(news_taken(array_keys($pages)), fn($r) => ! ($r->post_type === 'page' && get_post_meta($r->ID, NEWS_MIGRATED_META, true)));
    if ($clash) {
        WP_CLI::error('Local IDs already taken, resolve first: ' . implode(',', array_map(fn($r) => "{$r->ID} {$r->post_type}", $clash)));
    }

    foreach ($pages as $id => $config) {
        if (get_post_meta($id, NEWS_MIGRATED_META, true)) {
            wp_delete_post($id, true);
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
            // The old template drew its header from the featured image; here that's an Image Hero block.
            if ($thumb) {
                $hero = news_acf_block('acf/image-hero', \App\Blocks\ImageHero::class, [
                    'background_image' => $thumb,
                    'image_position' => 'center',
                    'show_heading' => '1',
                    'heading' => html_entity_decode($row->post_title, ENT_QUOTES),
                    'show_breadcrumbs' => '1',
                ], 'image_hero');
            } else {
                // No featured image (BMX) — a Text Hero, so the title still gets a proper hero.
                $hero = news_acf_block('acf/text-hero', \App\Blocks\TextHero::class, [
                    'heading_text' => html_entity_decode($row->post_title, ENT_QUOTES),
                    'heading_level' => 'h1',
                    'heading_style' => 'match',
                    'show_breadcrumbs' => '1',
                ], 'text_hero');
            }
            $hero['attrs']['align'] = 'full';
            $content = serialize_block($hero) . "\n\n" . $content;
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
    WP_CLI::success(count($pages) . ' pages imported.');

    return;
}

WP_CLI::error("Unknown mode: $mode");
