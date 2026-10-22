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
 *   pages        copy every other published page (optionally only comma-separated ids), keeping IDs,
 *                parents and menu order; adds a hero and puts the page on the Sticky Nav template
 *                (menu from the H2s); run after `attachments`
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

/**
 * Old text fields were hard-wrapped (a newline every few words, mostly pasted from Word).
 * Single newlines would render as <br>, so join them; blank lines stay as paragraph breaks.
 */
function news_unwrap_lines(string $text): string
{
    return trim(preg_replace('/(?<!\n)\n(?!\n)/', ' ', str_replace("\r\n", "\n", $text)));
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
    $text = esc_html(news_unwrap_lines((string) ($d['content'] ?? '')));
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

    $block = news_acf_block('acf/bullet-list', \App\Blocks\BulletList::class, $fields, 'bullet_list');
    // The block defaults to no padding; migrated lists get a Small gap below.
    $block['attrs']['style']['spacing']['padding'] = ['top' => '0', 'bottom' => 'var:preset|spacing|small'];

    return $block;
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
 * Old acf/card blocks (icon or image, title, text, link) -> one Card Grid of
 * "link" cards. A card sourced from a post takes that post's title, excerpt,
 * featured image and a link by ID (IDs are kept, so `?p=` resolves).
 */
function news_card_grid(array $cards, wpdb $src): array
{
    $fields = ['card_style' => 'link', 'cards' => count($cards)];
    foreach (array_values($cards) as $n => $card) {
        $d = $card['attrs']['data'] ?? [];
        if (($d['content_source'] ?? '') === 'post' && ! empty($d['content'])) {
            $post = $src->get_row($src->prepare("SELECT post_title, post_excerpt FROM {$src->posts} WHERE ID=%d", $d['content']));
            $d = [
                'title' => $post->post_title ?? '',
                'text' => $post->post_excerpt ?? '',
                'image' => (int) $src->get_var($src->prepare("SELECT meta_value FROM {$src->postmeta} WHERE post_id=%d AND meta_key='_thumbnail_id'", $d['content'])),
                'link' => ['title' => '', 'url' => home_url('/?p=' . (int) $d['content']), 'target' => ''],
            ];
        }
        $fields += [
            "cards_{$n}_image" => $d['icon'] ?? $d['image'] ?? $d['header_image'] ?? '',
            "cards_{$n}_heading_text" => html_entity_decode($d['title'] ?? '', ENT_QUOTES),
            "cards_{$n}_heading_level" => 'h3',
            "cards_{$n}_heading_style" => 'match',
            "cards_{$n}_body_text" => news_unwrap_lines($d['text'] ?? ''),
            "cards_{$n}_body_style" => 'body',
            "cards_{$n}_link" => $d['link'] ?? '',
        ];
    }

    return news_acf_block('acf/card-grid', \App\Blocks\CardGrid::class, $fields, 'card_grid');
}

/** The old card's link points at an uploaded file (map, guide) rather than a page. */
function news_card_file_id(array $card, wpdb $src): int
{
    $url = html_entity_decode($card['attrs']['data']['link']['url'] ?? '');
    if (! preg_match('#/app/uploads/(.+\.(?:pdf|docx?|xlsx?|zip))$#i', $url, $m)) {
        return 0;
    }

    return (int) $src->get_var($src->prepare("SELECT post_id FROM {$src->postmeta} WHERE meta_key='_wp_attached_file' AND meta_value=%s", $m[1]));
}

/**
 * Old acf/card blocks -> Link Card: files (maps, guides) become download cards,
 * anything else a link card whose CTA label is the old link text. The old
 * icon is the logo; large header photos have no place on these cards.
 */
function news_link_cards(array $cards, wpdb $src): array
{
    $fields = ['cards' => count($cards)];
    foreach (array_values($cards) as $i => $card) {
        $d = $card['attrs']['data'] ?? [];
        $fileId = news_card_file_id($card, $src);
        $file = $fileId ? $src->get_var($src->prepare("SELECT meta_value FROM {$src->postmeta} WHERE post_id=%d AND meta_key='_wp_attached_file'", $fileId)) : null;
        $path = $file ? wp_upload_dir()['basedir'] . '/' . $file : null;
        $link = $d['link'] ?? ['title' => '', 'url' => '', 'target' => ''];
        $fields += [
            "cards_{$i}_logo" => ($d['image_options'] ?? '') === 'icon' ? ($d['icon'] ?? '') : '',
            "cards_{$i}_title" => html_entity_decode(news_plain($d['title'] ?? ''), ENT_QUOTES),
            "cards_{$i}_description" => news_unwrap_lines(news_plain($d['text'] ?? '')),
            "cards_{$i}_cta_type" => $fileId ? 'download' : 'link',
            "cards_{$i}_link" => $fileId ? '' : $link,
            "cards_{$i}_file" => $fileId ?: '',
            "cards_{$i}_cta_label" => $link['title'] ?? 'Download',
            "cards_{$i}_file_size" => $path && file_exists($path) ? trim(strtoupper(pathinfo($file, PATHINFO_EXTENSION)) . ' ' . str_replace(' ', '', size_format(filesize($path), 1))) : '',
        ];
    }

    return news_acf_block('acf/link-card', \App\Blocks\LinkCard::class, $fields, 'link_card');
}

/**
 * One layout of old cards: Link Cards when any is a file download, or when the page asks
 * for them — except photo cards (every card has a title and header photo), which stay a
 * Card Grid so they sit side by side with their images.
 */
function news_card_layout(array $cards, wpdb $src, bool $linkCards): array
{
    $files = array_filter($cards, fn($c) => news_card_file_id($c, $src));
    $photos = ! array_filter($cards, fn($c) => empty($c['attrs']['data']['header_image']) || empty($c['attrs']['data']['title']));

    return $files || ($linkCards && ! $photos) ? news_link_cards($cards, $src) : news_card_grid($cards, $src);
}

/** A group/columns layout holding only old acf/card blocks -> one Card Grid. */
function news_old_cards(array $b, wpdb $src, bool $linkCards = false): ?array
{
    // Empty paragraphs (editor leftovers padding a column) don't count as content.
    $leaves = array_filter(news_flatten($b['innerBlocks']), fn($i) => ! $i['innerBlocks'] && ! news_is_blank($i) && ! ($i['blockName'] === 'core/paragraph' && news_plain($i['innerHTML']) === ''));
    if (! $leaves || array_filter($leaves, fn($i) => $i['blockName'] !== 'acf/card')) {
        return null;
    }

    return news_card_layout($leaves, $src, $linkCards);
}

/** Swap each outermost all-acf/card group/columns, at any depth, 1:1 for a Card Grid. */
function news_replace_old_cards(array $blocks, wpdb $src, array &$notes, bool $linkCards = false): array
{
    foreach ($blocks as &$b) {
        if (in_array($b['blockName'], ['core/group', 'core/columns'], true) && ($cards = news_old_cards($b, $src, $linkCards))) {
            $b = $cards;
            $notes[] = 'acf/card layout -> Card Grid';
        } elseif ($b['innerBlocks']) {
            $b['innerBlocks'] = news_replace_old_cards($b['innerBlocks'], $src, $notes, $linkCards);
        }
    }

    return $blocks;
}

/** An old illustration asset path (images/illustrations/figures/mountain_biker.png) as the block's field value. */
function news_illustration_key(string $path): string
{
    return strtolower(pathinfo($path, PATHINFO_FILENAME));
}

/**
 * Old acf/illustration(-divider) -> Illustration block: same character, objects and
 * blue-strip setting. A character the theme doesn't have (the old "Instructor and
 * girl" image is missing on the live site too) drops the block.
 */
function news_illustration(array $b): ?array
{
    $d = $b['attrs']['data'] ?? [];
    $character = news_illustration_key($d['illustration_figure'] ?? '');
    if (! isset(\App\Blocks\Illustration::CHARACTERS[$character])) {
        return null;
    }
    $fields = ['character' => $character];
    foreach ([1, 2, 3] as $n) {
        $object = news_illustration_key($d["illustration_object_$n"] ?? '');
        $fields["object_$n"] = isset(\App\Blocks\Illustration::OBJECTS[$object]) ? $object : '';
    }
    $fields['remove_blue_background'] = ! empty($d['remove_blue_background']) ? '1' : '0';

    return news_acf_block('acf/illustration', \App\Blocks\Illustration::class, $fields, 'illustration');
}

/** Accordion from the old FAQ block (question/answer repeater). */
function news_faq(array $b): array
{
    $d = $b['attrs']['data'] ?? [];
    $fields = ['heading' => '', 'items' => (int) ($d['questions'] ?? 0)];
    for ($i = 0; $i < $fields['items']; $i++) {
        $fields["items_{$i}_title"] = news_plain($d["questions_{$i}_question"] ?? '');
        $fields["items_{$i}_content"] = wpautop(news_unwrap_lines($d["questions_{$i}_answer"] ?? ''));
    }

    return news_acf_block('acf/accordion', \App\Blocks\Accordion::class, $fields, 'accordion');
}

/** Transcript -> an Accordion with one "Transcript" item. */
function news_transcript(array $b): array
{
    $d = $b['attrs']['data'] ?? [];

    return news_acf_block('acf/accordion', \App\Blocks\Accordion::class, [
        'heading' => '',
        'items' => 1,
        'items_0_title' => 'Transcript',
        'items_0_content' => wpautop(news_unwrap_lines($d['transcript'] ?? '')),
    ], 'accordion');
}

/** Timeline (numbered markers) from the old step-by-step block. */
function news_steps(array $b): array
{
    $d = $b['attrs']['data'] ?? [];
    $fields = [
        'heading' => news_plain($d['heading'] ?? ''),
        'intro' => news_plain($d['sub_heading'] ?? ''),
        'marker_size' => 'number',
        'steps' => (int) ($d['steps'] ?? 0),
    ];
    for ($i = 0; $i < $fields['steps']; $i++) {
        $fields["steps_{$i}_label"] = '';
        $fields["steps_{$i}_heading"] = news_plain($d["steps_{$i}_step_title"] ?? '');
        $fields["steps_{$i}_body"] = news_unwrap_lines(news_plain($d["steps_{$i}_step_content"] ?? ''));
    }

    return news_acf_block('acf/timeline', \App\Blocks\Timeline::class, $fields, 'timeline');
}

/** Testimonial Card Carousel from the old staggered content cards. A title becomes an H2 above it. */
function news_staggered_cards(array $b): array
{
    $d = $b['attrs']['data'] ?? [];
    $fields = ['cards' => (int) ($d['cards'] ?? 0)];
    for ($i = 0; $i < $fields['cards']; $i++) {
        $fields += [
            "cards_{$i}_photo" => $d["cards_{$i}_image"] ?? '',
            "cards_{$i}_name" => news_plain($d["cards_{$i}_author"] ?? ''),
            "cards_{$i}_info" => news_plain($d["cards_{$i}_subtitle"] ?? ''),
            "cards_{$i}_quote" => news_unwrap_lines(news_plain($d["cards_{$i}_quote"] ?? '')),
            "cards_{$i}_link" => '',
        ];
    }
    $carousel = news_acf_block('acf/testimonial-card-carousel', \App\Blocks\TestimonialCardCarousel::class, $fields, 'testimonial_card_carousel');
    if (! empty($d['has_blue_background'])) {
        $carousel['attrs']['backgroundColor'] = 'blue-light';
    }
    $title = news_plain($d['title'] ?? '');

    return $title ? [parse_blocks('<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html($title) . '</h2><!-- /wp:heading -->')[0], $carousel] : [$carousel];
}

/** A Google Drive "open?id=" link to a My Maps file is the map's embed ID. */
function news_map_url(string $url): string
{
    $parts = parse_url(html_entity_decode($url));
    parse_str($parts['query'] ?? '', $query);
    if (($parts['host'] ?? '') === 'drive.google.com' && ! empty($query['id'])) {
        return 'https://www.google.com/maps/d/embed?mid=' . $query['id'];
    }

    return html_entity_decode($url);
}

/** Map Embed from a Google map iframe (core/html) or a Google-provider core/embed; null for anything else. */
function news_map_embed(array $b, string $title): ?array
{
    $url = null;
    if ($b['blockName'] === 'core/html' && preg_match('#<iframe[^>]+src="([^"]+)"#i', $b['innerHTML'], $m) && str_contains($m[1], 'google.com/maps')) {
        $url = $m[1];
        $title = preg_match('#<iframe[^>]+title="([^"]+)"#i', $b['innerHTML'], $t) ? html_entity_decode($t[1]) : $title;
    } elseif ($b['blockName'] === 'core/embed' && ($b['attrs']['providerNameSlug'] ?? '') === 'google') {
        $url = $b['attrs']['url'] ?? null;
    }
    if (! $url) {
        return null;
    }

    return news_acf_block('acf/map-embed', \App\Blocks\MapEmbed::class, [
        'map_url' => news_map_url($url),
        'map_title' => $title ?: 'Map',
        'height' => 'large',
    ], 'map_embed');
}

/**
 * Heading size classes (h3/h4) and centred text mean nothing in this theme —
 * the heading level stays, the old visual override and alignment go.
 */
function news_clean_text(array $b): array
{
    unset($b['attrs']['align'], $b['attrs']['textAlign']);
    if (isset($b['attrs']['className'])) {
        $b['attrs']['className'] = trim(preg_replace('/\bh[1-6]\b/', '', $b['attrs']['className']));
        if ($b['attrs']['className'] === '') {
            unset($b['attrs']['className']);
        }
    }
    $clean = fn($html) => is_string($html) ? preg_replace(['/ has-text-align-(center|left|right)/', '/(class="[^"]*?) h[1-6](?=[ "])/'], '', $html) : $html;
    if ($b['blockName'] === 'core/paragraph') {
        // A line break between two lowercase letters is a hard wrap mid-sentence; addresses
        // (a capital after the break) keep theirs.
        $unwrap = fn($html) => is_string($html) ? preg_replace('#(?<=[a-z])<br\s*/?>(?=[a-z])#', ' ', $html) : $html;
        $b['innerHTML'] = $unwrap($b['innerHTML']);
        $b['innerContent'] = array_map($unwrap, $b['innerContent']);
    }
    $b['innerHTML'] = $clean($b['innerHTML']);
    $b['innerContent'] = array_map($clean, $b['innerContent']);

    return $b;
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

/**
 * Replace each reusable-block reference (core/block) with the blocks it points
 * at, at any depth, so they're converted like the rest of the page. Reusable
 * blocks can nest other references.
 */
function news_inline_reusable(array $blocks, wpdb $src, array &$notes, int $depth = 0): array
{
    $out = [];
    foreach ($blocks as $b) {
        if ($b['blockName'] === 'core/block') {
            $ref = (int) ($b['attrs']['ref'] ?? 0);
            $content = $depth < 5 ? $src->get_var($src->prepare("SELECT post_content FROM {$src->posts} WHERE ID=%d AND post_type='wp_block'", $ref)) : null;
            if ($content === null) {
                $notes[] = "reusable block $ref not inlined (missing or nested too deep)";
                $out[] = $b;

                continue;
            }
            $notes[] = "reusable block $ref inlined";
            array_push($out, ...news_inline_reusable(parse_blocks($content), $src, $notes, $depth + 1));

            continue;
        }
        if ($b['innerBlocks']) {
            // innerContent holds a null per child; one child can become several blocks, so rebuild the slots.
            $children = [];
            $content = [];
            $i = 0;
            foreach ($b['innerContent'] as $chunk) {
                if ($chunk !== null) {
                    $content[] = $chunk;

                    continue;
                }
                foreach (news_inline_reusable([$b['innerBlocks'][$i++]], $src, $notes, $depth) as $child) {
                    $children[] = $child;
                    $content[] = null;
                }
            }
            $b['innerBlocks'] = $children;
            $b['innerContent'] = $content;
        }
        $out[] = $b;
    }

    return $out;
}

/**
 * Every heading gets the theme's default Heading colour (Blue medium), as a freshly
 * inserted one would. The colour has to be in both the attribute and the saved
 * markup's classes, or the editor flags the block as invalid.
 */
function news_colour_headings(array $blocks): array
{
    foreach ($blocks as &$b) {
        if ($b['blockName'] === 'core/heading' && empty($b['attrs']['textColor']) && empty($b['attrs']['style']['color'])) {
            $b['attrs']['textColor'] = 'blue-medium';
            $classes = ' has-blue-medium-color has-text-color';
            $addClasses = fn($html) => is_string($html)
                ? (preg_match('/^(\s*<h[1-6][^>]*\sclass=")/', $html) ? preg_replace('/^(\s*<h[1-6][^>]*\sclass="[^"]*)"/', '$1' . $classes . '"', $html) : preg_replace('/^(\s*<h[1-6])/', '$1 class="wp-block-heading' . $classes . '"', $html))
                : $html;
            $b['innerHTML'] = $addClasses($b['innerHTML']);
            $b['innerContent'] = array_map($addClasses, $b['innerContent']);
        }
        if ($b['innerBlocks']) {
            $b['innerBlocks'] = news_colour_headings($b['innerBlocks']);
        }
    }

    return $blocks;
}

/**
 * Card Grids (or Link Cards) with nothing but whitespace between them (the old
 * layouts put one row of cards per group) become one block, so a lone card isn't left stranded.
 */
function news_merge_card_grids(array $blocks, array &$notes): array
{
    $out = [];
    $last = null;
    foreach ($blocks as $b) {
        if (news_is_blank($b)) {
            $out[] = $b;

            continue;
        }
        if (in_array($b['blockName'], ['acf/card-grid', 'acf/link-card'], true) && $last !== null && $out[$last]['blockName'] === $b['blockName']) {
            $base = (int) $out[$last]['attrs']['data']['cards'];
            foreach ($b['attrs']['data'] as $key => $value) {
                if (preg_match('/^(_?)cards_(\d+)_(.+)$/', $key, $m)) {
                    $out[$last]['attrs']['data'][$m[1] . 'cards_' . ($base + (int) $m[2]) . '_' . $m[3]] = $value;
                }
            }
            $out[$last]['attrs']['data']['cards'] = $base + (int) $b['attrs']['data']['cards'];
            // Whitespace between the two grids goes with them.
            while (news_is_blank(end($out))) {
                array_pop($out);
            }
            $notes[] = 'adjacent card blocks merged';

            continue;
        }
        $out[] = $b;
        $last = count($out) - 1;
    }

    return $out;
}

function news_transform(string $content, wpdb $src, array &$notes, bool $keepCalendars = false, bool $linkCards = false): string
{
    // Old card layouts are spotted by their wrapping group, so swap those
    // first; then lift every block out of its group so the loop below
    // converts blocks at any depth, not just the top level.
    $in = news_unwrap_groups(news_replace_old_cards(news_inline_reusable(parse_blocks($content), $src, $notes), $src, $notes, $linkCards));
    $out = [];
    $bikes = [];
    $bikeSlot = null;
    $timetable = false;

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
            case 'acf/faq':
                $out[] = news_faq($b);
                break;
            case 'acf/transcript':
                $out[] = news_transcript($b);
                break;
            case 'acf/step-by-step':
                $out[] = news_steps($b);
                break;
            case 'acf/staggered-content-cards':
                array_push($out, ...news_staggered_cards($b));
                break;
            case 'acf/calendar':
                if ($keepCalendars) {
                    $out[] = $b;
                    break;
                }
                // One Events Timetable shows every timetable, so the per-term "… Timetable" headings go too.
                while ($out && news_is_blank(end($out))) {
                    array_pop($out);
                }
                if ($out && end($out)['blockName'] === 'core/heading' && str_contains(strtolower(end($out)['innerHTML']), 'timetable')) {
                    array_pop($out);
                }
                if (! $timetable) {
                    $out[] = news_acf_block('acf/events-timetable', \App\Blocks\EventsTimetable::class, ['source' => 'timetables', 'layout' => 'tabs'], 'events_timetable');
                    $notes[] = 'acf/calendar blocks -> one Events Timetable (event timetables)';
                    $timetable = true;
                }
                break;
            case 'acf/divider':
                $out[] = parse_blocks('<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->')[0];
                break;
            case 'acf/illustration':
            case 'acf/illustration-divider':
                if ($illustration = news_illustration($b)) {
                    $out[] = $illustration;
                } else {
                    $notes[] = 'illustration dropped (character not in the theme library)';
                }
                break;
            case 'acf/card':
                // Consecutive loose cards share one grid.
                $run = [$b];
                while (isset($in[$i + 1]) && ($in[$i + 1]['blockName'] === 'acf/card' || (news_is_blank($in[$i + 1]) && ($in[$i + 2]['blockName'] ?? null) === 'acf/card'))) {
                    $i++;
                    if ($in[$i]['blockName']) {
                        $run[] = $in[$i];
                    }
                }
                $out[] = news_card_layout($run, $src, $linkCards);
                $notes[] = count($run) . ' loose acf/card -> Card Grid / Link Card';
                break;
            case 'core/html':
            case 'core/embed':
                $prev = null;
                foreach (array_reverse($out) as $p) {
                    if ($p && ! news_is_blank($p)) {
                        $prev = $p;

                        break;
                    }
                }
                $title = $prev && $prev['blockName'] === 'core/heading' ? news_plain($prev['innerHTML']) : '';
                $map = news_map_embed($b, $title);
                $out[] = $map ?? $b;
                if ($map) {
                    $notes[] = 'Google map -> Map Embed';
                }
                break;
            case 'core/paragraph':
            case 'core/heading':
                $out[] = news_clean_text($b);
                break;
            case 'acf/bike-card':
                $bikes[] = $b;
                $bikeSlot ??= count($out);
                $out[] = null; // placeholder, filled once all cards are collected
                break;
            default:
                if ($b['blockName'] === 'acf/form') {
                    // Same name as this theme's Form block, but the old data is a Contact Form 7 selector.
                    $notes[] = 'acf/form is an old CF7 form (needs the Gravity Forms conversion)';
                } elseif ($b['blockName'] && ! str_starts_with($b['blockName'], 'core/') && ! WP_Block_Type_Registry::get_instance()->is_registered($b['blockName'])) {
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

    return serialize_blocks(news_colour_headings(news_tighten_after_headings(news_merge_card_grids($out, $notes))));
}

/**
 * The old theme wrapped sections in core/group purely for its own style
 * classes (is-style-lightblue, is-style-block-fullwidth-bg), which mean
 * nothing here — lift their contents out (at any depth) so each block gets
 * converted and spaces itself. Columns without images are lifted out too;
 * columns holding images stay for news_nested() / news_image_columns().
 */
function news_unwrap_groups(array $blocks): array
{
    $out = [];
    foreach ($blocks as $b) {
        if (($b['blockName'] ?? null) === 'core/group') {
            array_push($out, ...news_unwrap_groups($b['innerBlocks']));
        } elseif (($b['blockName'] ?? null) === 'core/columns' && ! array_filter(news_flatten($b['innerBlocks']), fn($i) => $i['blockName'] === 'core/image')) {
            // Side-by-side layout is lost; the blocks keep their order. Columns with images stay (Two Column Content).
            foreach ($b['innerBlocks'] as $column) {
                array_push($out, ...news_unwrap_groups($column['innerBlocks']));
            }
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

/** Cycling centres > Bristol Cycling Centre > What's on, copied with their IDs and URLs by `cycling-centre`. */
const CC_PAGES = [308, 309, 796];

/** Pages `pages` mode leaves alone: the old News archive, and CF7 leftovers (test page, thank-you, survey). */
const NEWS_PAGES_SKIP = [59, 1985, 1550, 1717];

/** Landing pages, held back until their hero/carousel/card blocks have conversions. */
const NEWS_PAGES_DEFERRED = [220, 241];

/** Pages whose cards are all downloads/links to elsewhere: every card layout becomes Link Cards. */
const NEWS_LINK_CARD_PAGES = [701];

/**
 * Published pages the `pages` mode copies: everything except what other modes own
 * (map-pages, cycling-centre), the skip list and the held-back landing pages.
 */
function news_page_ids(wpdb $src, ?array $only): array
{
    $ids = array_values(array_diff(
        $src->get_col("SELECT ID FROM {$src->posts} WHERE post_type='page' AND post_status='publish' ORDER BY ID"),
        array_map('strval', [...array_keys(NEWS_MAP_PAGES), 6669, ...CC_PAGES, ...NEWS_PAGES_SKIP, ...NEWS_PAGES_DEFERRED]),
    ));

    return $only ? array_values(array_intersect($ids, array_map('strval', $only))) : $ids;
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
    foreach ([...$postIds, ...$caseStudyIds, ...array_keys(array_filter(NEWS_MAP_PAGES)), ...news_page_ids($src, $only)] as $id) {
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
    $pages = news_page_ids($src, $only);
    echo "\nPages (" . count($pages) . ") — local ID collisions, then H2 count (the Sticky Nav menu's items):\n";
    $pageClash = array_filter(news_taken($pages), fn($r) => ! ($r->post_type === 'page' && get_post_meta($r->ID, NEWS_MIGRATED_META, true)));
    foreach ($pageClash ?: [null] as $r) {
        echo $r ? "  CLASH {$r->ID} {$r->post_type} \"{$r->post_title}\"\n" : "  no collisions\n";
    }
    $pageNotes = [];
    foreach ($pages as $id) {
        $ignored = [];
        $page = news_page_content($src, (int) $id, $replaceUrls, $ignored);
        $bad = array_merge($bad, news_bad_keys($page['content']));
        foreach ($ignored as $n) {
            $pageNotes[] = "$id: $n";
        }
        echo "  $id ({$page['h2s']} H2s)\n";
    }
    echo "\nPage transform notes:\n  " . (implode("\n  ", array_unique($pageNotes)) ?: 'none') . "\n";
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
    foreach (news_page_ids($src, $only) as $id) {
        $notes = [];
        $page = news_page_content($src, (int) $id, $replaceUrls, $notes);
        $name = $src->get_var($src->prepare("SELECT post_name FROM {$src->posts} WHERE ID=%d", $id));
        file_put_contents("$dir/page-$id-$name.html", $page['content']);
    }
    WP_CLI::success(count($postIds) . ' posts and ' . count(news_page_ids($src, $only)) . " pages written to $dir");

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
 * The old template's featured-image header as an Image Hero, or a Text Hero
 * when there's no image (e.g. BMX).
 */
function news_page_hero(string $title, int $thumb, bool $breadcrumbs = true): array
{
    $title = html_entity_decode($title, ENT_QUOTES);
    $hero = $thumb
        ? news_acf_block('acf/image-hero', \App\Blocks\ImageHero::class, [
            'background_image' => $thumb,
            'image_position' => 'center',
            'show_heading' => '1',
            'heading' => $title,
            'show_breadcrumbs' => $breadcrumbs ? '1' : '0',
        ], 'image_hero')
        : news_acf_block('acf/text-hero', \App\Blocks\TextHero::class, [
            'heading_text' => $title,
            'heading_level' => 'h1',
            'heading_style' => 'match',
            'show_breadcrumbs' => $breadcrumbs ? '1' : '0',
        ], 'text_hero');
    $hero['attrs']['align'] = 'full';

    return $hero;
}

/**
 * A page's converted content with its hero in front, plus the template it should use.
 * Every page goes on Sticky Nav, which builds its menu from the H2s (none = no menu).
 *
 * @return array{content: string, template: string, h2s: int}
 */
function news_page_content(wpdb $src, int $id, callable $replaceUrls, array &$notes): array
{
    $row = $src->get_row($src->prepare("SELECT post_title, post_content FROM {$src->posts} WHERE ID=%d", $id));
    $thumb = (int) $src->get_var($src->prepare("SELECT meta_value FROM {$src->postmeta} WHERE post_id=%d AND meta_key='_thumbnail_id'", $id));
    $blocks = parse_blocks(news_transform($row->post_content, $src, $notes, false, in_array($id, NEWS_LINK_CARD_PAGES, true)));

    $h2s = 0;
    $walk = function (array $list) use (&$walk, &$h2s) {
        foreach ($list as $b) {
            if ($b['blockName'] === 'core/heading' && ($b['attrs']['level'] ?? 2) === 2) {
                $h2s++;
            }
            $walk($b['innerBlocks']);
        }
    };
    $walk($blocks);

    // Every page uses Sticky Nav, as on the live site (pages without H2s just have no menu).
    // It draws its own breadcrumbs, so the hero's would show twice.
    return [
        'content' => $replaceUrls(serialize_block(news_page_hero($row->post_title, $thumb, false)) . "\n\n" . serialize_blocks($blocks)),
        'template' => 'template-sticky-nav.blade.php',
        'h2s' => $h2s,
    ];
}

if ($mode === 'pages') {
    $ids = news_page_ids($src, $only);
    $clash = array_filter(news_taken($ids), fn($r) => ! ($r->post_type === 'page' && get_post_meta($r->ID, NEWS_MIGRATED_META, true)));
    if ($clash) {
        WP_CLI::error('Local IDs already taken, resolve first (see `check`): ' . implode(',', array_map(fn($r) => "{$r->ID} {$r->post_type}", $clash)));
    }

    foreach ($ids as $id) {
        $id = (int) $id;
        // Re-running replaces earlier imports in place (keeps nav menu items pointing at the page).
        if (get_post_meta($id, NEWS_MIGRATED_META, true)) {
            news_remove_page_import($id);
        }
        $notes = [];
        $page = news_page_content($src, $id, $replaceUrls, $notes);
        news_copy_row($src, $id, $replaceUrls, ['post_content' => $page['content']]);
        news_copy_meta($src, $id, $skipMeta, fn($k, $v) => in_array($k, ['_thumbnail_id', '_wp_old_slug'], true) || str_starts_with($k, '_yoast_wpseo_') ? $v : null);
        update_post_meta($id, NEWS_MIGRATED_META, 1);
        if ($page['template']) {
            update_post_meta($id, '_wp_page_template', $page['template']);
        }
        clean_post_cache($id);
        echo "imported $id" . ($page['template'] ? ' (sticky nav)' : '') . ($notes ? ' (' . implode('; ', array_unique($notes)) . ')' : '') . "\n";
    }
    WP_CLI::success(count($ids) . ' pages imported.');

    return;
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

/**
 * An old term as an event timetable schedule (App\EventTimetable\Schedule):
 * the weekly slots become sessions, skipping the timetable fillers.
 */
function cc_term_schedule(array $term, int $category): array
{
    $weekdays = array_flip(['', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
    $sessions = [];
    foreach ($term['slots'] as $slot) {
        if (in_array($slot['event'], CC_TIMETABLE_FILLERS, true) || ! $slot['start'] || ! $slot['end']) {
            continue;
        }
        $sessions[] = [
            'id' => wp_generate_uuid4(),
            'event' => $slot['event'],
            'weekday' => $weekdays[$slot['day']],
            'start' => substr($slot['start'], 0, 5),
            'end' => substr($slot['end'], 0, 5),
        ];
    }
    $ymd = fn(string $d) => DateTimeImmutable::createFromFormat('Ymd', $d)->format('Y-m-d');

    return [
        'start' => $ymd($term['start']),
        'end' => $ymd($term['end']),
        'category' => $category,
        'sessions' => $sessions,
        'closures' => [],
        'cancellations' => [],
        'extras' => [],
    ];
}

/**
 * A plain "Free"/"FREE" price becomes 0, what MEC's "This event is free"
 * box saves, so every free event is set up the same way.
 */
function cc_cost(string $price): string
{
    $price = trim($price);

    return strcasecmp($price, 'free') === 0 ? '0' : $price;
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
        // Next session + price now come from the single event template.
        if ($name === 'acf/upcoming-sessions') {
            continue;
        }
        // Old CF7 forms (Bristol Cycling Centre contact/newsletter) need rebuilding in Gravity Forms.
        if (in_array($name, ['acf/form', 'contact-form-7/contact-form-selector'], true)) {
            $notes[] = "dropped $name (CF7 form, rebuild in Gravity Forms)";

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

    // Earlier runs' terms go first: deleting one removes the dates it generated.
    foreach (get_posts(['post_type' => \App\EventTimetable\EventTimetable::POST_TYPE, 'post_status' => 'any', 'numberposts' => -1, 'meta_key' => NEWS_MIGRATED_META, 'fields' => 'ids']) as $termId) {
        wp_delete_post($termId, true);
    }

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

        // Saved undated, on its old publish date: the event timetables below add the sessions.
        $start = substr($row->post_date, 0, 10);
        $price = (string) $src->get_var($src->prepare("SELECT meta_value FROM {$src->postmeta} WHERE post_id=%d AND meta_key='price'", $id));

        $saved = $mec->save_event([
            'title' => html_entity_decode($row->post_title, ENT_QUOTES),
            // wp_insert_post() unslashes, which would eat the \u003c escapes in block JSON.
            'content' => wp_slash($replaceUrls($content)),
            'status' => 'publish',
            'author' => 1,
            'start' => $start,
            'end' => $start,
            'start_time_hour' => '12',
            'start_time_minutes' => '00',
            'start_time_ampm' => 'AM',
            'end_time_hour' => '12',
            'end_time_minutes' => '00',
            'end_time_ampm' => 'AM',
            'allday' => 0,
            'date' => ['hide_time' => 1],
            'repeat_status' => 0,
            'repeat_type' => '',
            'interval' => null,
            'location_id' => $location,
            'meta' => [
                'mec_cost' => cc_cost($price),
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
        // Placeholder date until a term adds sessions (App\Support\EventSessions::forDisplay()).
        update_post_meta($id, '_event_undated', 1);
        clean_post_cache($id);

        echo "imported $id {$row->post_name}" . ($notes ? ' (' . implode('; ', array_unique($notes)) . ')' : '') . "\n";
    }

    // The old schedule's current terms, as published event timetables; publishing syncs their sessions into MEC.
    foreach (cc_terms($src) as $term) {
        $name = trim(strtok($term['title'], ':')) ?: 'Term';
        $termId = wp_insert_post(['post_type' => \App\EventTimetable\EventTimetable::POST_TYPE, 'post_title' => $name, 'post_status' => 'publish']);
        update_post_meta($termId, \App\EventTimetable\EventTimetable::META, cc_term_schedule($term, $category));
        update_post_meta($termId, NEWS_MIGRATED_META, 1);
        $notices = \App\EventTimetable\MecSync::sync($termId);
        echo "event timetable $termId \"$name\": " . count(cc_term_schedule($term, $category)['sessions']) . ' weekly sessions' . ($notices ? ' (' . implode('; ', $notices) . ')' : '') . "\n";
        // Bad old data (e.g. a slot ending before it starts) is skipped by the sync; say which, to fix in the editor.
        $schedule = get_post_meta($termId, \App\EventTimetable\EventTimetable::META, true);
        foreach (\App\EventTimetable\Schedule::validate($schedule)['errors'] as $error) {
            $session = current(array_filter($schedule['sessions'], fn($x) => $x['id'] === $error['ref']));
            WP_CLI::warning("\"$name\": {$error['message']}" . ($session ? ' ' . get_the_title($session['event']) . ", day {$session['weekday']} {$session['start']}–{$session['end']}" : ''));
        }
    }
    foreach ($ids as $id) {
        $count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}mec_dates WHERE post_id=%d", $id));
        if (! get_post_meta($id, '_event_undated', true)) {
            echo "  $id " . get_post_field('post_name', $id) . ": $count sessions\n";
        }
    }

    // The pages above the sessions. What's on had one acf/calendar block per
    // term, each under a "… Timetable" heading; they become a single Events
    // Timetable showing every current event timetable.
    foreach (CC_PAGES as $id) {
        if (get_post_meta($id, NEWS_MIGRATED_META, true)) {
            news_remove_page_import($id);
        }
        $row = $src->get_row($src->prepare("SELECT post_title, post_content FROM {$src->posts} WHERE ID=%d", $id));
        $thumb = (int) $src->get_var($src->prepare("SELECT meta_value FROM {$src->postmeta} WHERE post_id=%d AND meta_key='_thumbnail_id'", $id));
        $notes = [];
        $blocks = [];
        $timetableAdded = false;
        foreach (parse_blocks(news_transform(serialize_blocks(cc_strip_blocks(parse_blocks($row->post_content), $notes)), $src, $notes, true)) as $b) {
            if ($b['blockName'] !== 'acf/calendar') {
                $blocks[] = $b;

                continue;
            }
            // Drop the per-term heading above it (skipping parse_blocks' whitespace blocks).
            for ($i = count($blocks) - 1; $i >= 0 && news_is_blank($blocks[$i]); $i--);
            if ($i >= 0 && $blocks[$i]['blockName'] === 'core/heading' && str_contains(strtolower($blocks[$i]['innerHTML']), 'timetable')) {
                array_splice($blocks, $i, 1);
            }
            if ($timetableAdded) {
                continue;
            }
            $blocks[] = parse_blocks("<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">Timetable</h2>\n<!-- /wp:heading -->")[0];
            $blocks[] = news_acf_block('acf/events-timetable', \App\Blocks\EventsTimetable::class, [
                'source' => 'terms',
                'layout' => 'tabs',
                'category' => $category,
            ], 'events_timetable');
            $timetableAdded = true;
            $notes[] = 'acf/calendar blocks -> one Events Timetable (event timetables)';
        }
        $notes = array_filter($notes, fn($n) => $n !== 'UNMAPPED acf/calendar');

        // Every page uses Sticky Nav, which draws its own breadcrumbs. What's on has no hero
        // block: the template falls back to a Text Hero with the page title.
        $content = ($id === 796 ? '' : serialize_block(news_page_hero($row->post_title, $thumb, false)) . "\n\n") . serialize_blocks($blocks);
        news_copy_row($src, $id, $replaceUrls, ['post_content' => $replaceUrls($content)]);
        news_copy_meta($src, $id, $skipMeta, fn($k, $v) => $k === '_thumbnail_id' || str_starts_with($k, '_yoast_wpseo_') ? $v : null);
        update_post_meta($id, NEWS_MIGRATED_META, 1);
        update_post_meta($id, '_wp_page_template', 'template-sticky-nav.blade.php');
        clean_post_cache($id);
        echo "imported page $id " . ($notes ? '(' . implode('; ', array_unique($notes)) . ')' : '') . "\n";
    }

    WP_CLI::success(count($ids) . ' Cycling Centre sessions and ' . count(CC_PAGES) . ' pages imported.');

    return;
}

/*
|--------------------------------------------------------------------------
| One-off events -> Modern Events Calendar
|--------------------------------------------------------------------------
*/

if ($mode === 'events') {
    if (! class_exists('MEC')) {
        WP_CLI::error('Modern Events Calendar must be active.');
    }
    $mec = MEC::getInstance('app.libraries.main');

    // Newest first, by event date, so `events` with no ids still has a sensible order.
    $ids = $src->get_col("SELECT p.ID FROM {$src->posts} p JOIN {$src->postmeta} m ON m.post_id = p.ID AND m.meta_key = 'start_date' WHERE p.post_type = 'event' AND p.post_status = 'publish' ORDER BY m.meta_value DESC");
    if ($only) {
        $ids = array_values(array_intersect($ids, $only));
    }
    $clash = array_filter(news_taken($ids), fn($r) => ! ($r->post_type === 'mec-events' && get_post_meta($r->ID, NEWS_MIGRATED_META, true)));
    if ($clash) {
        WP_CLI::error('Local IDs already taken, resolve first: ' . implode(',', array_map(fn($r) => "{$r->ID} {$r->post_type}", $clash)));
    }

    foreach ($ids as $id) {
        if (get_post_meta($id, NEWS_MIGRATED_META, true)) {
            wp_delete_post($id, true);
        }
        $row = $src->get_row($src->prepare("SELECT post_name, post_date, post_date_gmt, post_title, post_content FROM {$src->posts} WHERE ID=%d", $id));
        $meta = fn(string $key) => trim((string) $src->get_var($src->prepare("SELECT meta_value FROM {$src->postmeta} WHERE post_id=%d AND meta_key=%s", $id, $key)));
        $notes = [];
        $content = news_transform(serialize_blocks(cc_strip_blocks(parse_blocks($row->post_content), $notes)), $src, $notes);

        // Old ACF fields: start_date "Y-m-d H:i:s", end_date "Ymd" (optional), end_time "H:i:s" (optional).
        [$startDate, $startTime] = explode(' ', $meta('start_date')) + [1 => '00:00:00'];
        $endDate = $meta('end_date') ? DateTimeImmutable::createFromFormat('Ymd', $meta('end_date'))->format('Y-m-d') : $startDate;
        $endTime = $meta('end_time');
        // A midnight start with no end time was the old site's "all day".
        $allDay = $startTime === '00:00:00' && $endTime === '';
        [$sh, $sm, $sa] = cc_time_parts($startTime);
        [$eh, $em, $ea] = cc_time_parts($endTime ?: '23:59:00');

        $location = $meta('location');
        // Named by the part before the first comma, so "Bristol Cycling Centre, Old
        // Whitchurch…" reuses that location; the full text is a new location's address.
        $locationName = trim(strtok($location, ','));
        $locationId = $location ? cc_term('mec_location', $locationName, sanitize_title($locationName), get_term_by('slug', sanitize_title($locationName), 'mec_location') ? [] : ['address' => $location]) : 1;

        news_copy_row($src, $id, $replaceUrls, ['post_type' => 'mec-events', 'post_content' => '']);
        clean_post_cache($id);

        $saved = $mec->save_event([
            'title' => html_entity_decode($row->post_title, ENT_QUOTES),
            // wp_insert_post() unslashes, which would eat the \u003c escapes in block JSON.
            'content' => wp_slash($replaceUrls($content)),
            'status' => 'publish',
            'author' => 1,
            'start' => $startDate,
            'end' => $endDate,
            'start_time_hour' => $sh,
            'start_time_minutes' => $sm,
            'start_time_ampm' => $sa,
            'end_time_hour' => $eh,
            'end_time_minutes' => $em,
            'end_time_ampm' => $ea,
            'allday' => $allDay ? 1 : 0,
            'repeat_status' => 0,
            'repeat_type' => '',
            'interval' => null,
            'location_id' => $locationId,
            'meta' => ['mec_cost' => cc_cost($meta('cost'))],
        ], $id);
        if (is_wp_error($saved)) {
            WP_CLI::warning("$id: " . $saved->get_error_message());

            continue;
        }

        $wpdb->update($wpdb->posts, [
            'post_name' => $row->post_name,
            'post_date' => $row->post_date,
            'post_date_gmt' => $row->post_date_gmt,
        ], ['ID' => $id]);
        news_copy_meta($src, $id, $skipMeta, fn($k, $v) => in_array($k, ['_thumbnail_id', '_wp_old_slug'], true) || str_starts_with($k, '_yoast_wpseo_') ? $v : null);

        // Same category slugs as the old event_category terms (created by `cycling-centre`, or here).
        $cats = $src->get_results($src->prepare("SELECT t.name, t.slug FROM {$src->term_relationships} r JOIN {$src->term_taxonomy} tt USING(term_taxonomy_id) JOIN {$src->terms} t USING(term_id) WHERE r.object_id=%d AND tt.taxonomy='event_category'", $id));
        wp_set_object_terms($id, array_map(fn($t) => cc_term('mec_category', html_entity_decode($t->name), $t->slug), $cats), 'mec_category');
        if ($locationId !== 1) {
            wp_set_object_terms($id, [$locationId], 'mec_location');
        }
        update_post_meta($id, NEWS_MIGRATED_META, 1);
        clean_post_cache($id);

        echo "imported $id {$row->post_name}: $startDate" . ($allDay ? ' all day' : " $startTime-" . ($endTime ?: '?')) . ($endDate !== $startDate ? " to $endDate" : '') . ($notes ? ' (' . implode('; ', array_unique($notes)) . ')' : '') . "\n";
    }
    WP_CLI::success(count($ids) . ' one-off events imported into MEC.');

    return;
}

WP_CLI::error("Unknown mode: $mode");
