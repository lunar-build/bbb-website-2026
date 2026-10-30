<?php

/** Nav menu location → nested [{ label, href, current, children }] (header and primary-nav views). */
function menu_items_to_array(string $location): array
{
    $locations = get_nav_menu_locations();

    if (empty($locations[$location])) {
        return [];
    }

    $menu_items = wp_get_nav_menu_items($locations[$location]);

    if (! $menu_items) {
        return [];
    }

    $by_parent = [];

    foreach ($menu_items as $item) {
        $by_parent[(int) $item->menu_item_parent][] = $item;
    }

    $build = function (int $parent_id) use (&$build, $by_parent): array {
        return array_map(fn($item) => [
            // Page-linked items take the page title, which wptexturize turns into `&#038;`; Blade escapes it again.
            'label' => html_entity_decode($item->title, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'href' => $item->url,
            'current' => (bool) $item->current,
            'children' => $build((int) $item->ID),
        ], $by_parent[$parent_id] ?? []);
    };

    return $build(0);
}

/** Nav menu location → footer columns: one per top-level item, its children as links. */
function menu_items_to_footer_columns(string $location): array
{
    $items = menu_items_to_array($location);

    return array_map(fn($item) => [
        'title' => $item['label'],
        'links' => array_map(fn($child) => [
            'label' => $child['label'],
            'href' => $child['href'],
        ], $item['children']),
    ], $items);
}

/** Theme Options "Legal Links" repeater → `{ label, href }[]`, independent of the footer menu. */
function legal_links_from_options(): array
{
    $rows = get_field('legal_links', 'option') ?: [];

    return array_map(fn($row) => [
        'label' => $row['label'],
        'href' => $row['href'],
    ], $rows);
}

/** Theme Options "404 page" quick links → `{ label, href, target }[]`, falling back to a default set when empty. */
function not_found_links_from_options(): array
{
    $links = array_map(
        fn($row) => normalize_link($row['link'] ?? null),
        get_field('not_found_links', 'option') ?: [],
    );

    $links = array_values(array_filter($links, fn($link) => $link['url'] && $link['title']));

    if (! $links) {
        $postsPage = (int) get_option('page_for_posts');
        // The planner is a homepage block with no page of its own, so follow the header CTA's link.
        $plannerUrl = normalize_link(get_field('cta', 'option'))['url'] ?: home_url('/');

        $links = array_filter([
            ['title' => __('Home', 'sage'), 'url' => home_url('/')],
            ['title' => __('Journey planner', 'sage'), 'url' => $plannerUrl],
            ['title' => __('What’s on', 'sage'), 'url' => get_post_type_archive_link('mec-events')],
            ['title' => __('News', 'sage'), 'url' => $postsPage ? get_permalink($postsPage) : null],
        ], fn($link) => $link['url']);

        $links = array_map(fn($link) => $link + ['target' => ''], array_values($links));
    }

    return array_map(fn($link) => [
        'label' => $link['title'],
        'href' => $link['url'],
        'target' => $link['target'],
    ], $links);
}

/**
 * Theme Options "Social" fields → `{ platform, url }[]`, skipping blanks. `platform` is
 * the field slug (e.g. 'facebook'), so it maps to an icon deterministically (see
 * app/Options/ThemeOptions.php for why these aren't a repeater).
 */
function social_links_from_options(): array
{
    $platforms = ['facebook', 'instagram', 'x', 'linkedin', 'youtube', 'tiktok'];

    $links = array_map(fn($platform) => [
        'platform' => $platform,
        'url' => get_field("{$platform}_url", 'option'),
    ], $platforms);

    return array_values(array_filter($links, fn($link) => ! empty($link['url'])));
}

/**
 * Coerce an ACF `link` value to an array — legacy/incomplete rows can store a string or
 * nothing, which would fatal on array access. (CardRow.php has a near-identical
 * normalizeLink() predating this.)
 */
function normalize_link($link): array
{
    if (is_array($link)) {
        return $link + ['title' => '', 'url' => '', 'target' => ''];
    }

    return ['title' => '', 'url' => (string) ($link ?? ''), 'target' => ''];
}

/** ACF `choices` (`['slug' => 'Label']`) from every SVG in resources/svg/, for `<x-icon>`. Glob-based, so new SVGs appear automatically. */
function svg_icon_choices(): array
{
    $files = glob(get_theme_file_path('resources/svg/*.svg')) ?: [];

    $choices = [];

    foreach ($files as $file) {
        $slug = basename($file, '.svg');
        $choices[$slug] = ucwords(str_replace(['-', '_'], ' ', $slug));
    }

    return $choices;
}

/** ACF `choices` from the theme.json colour palette, for background pickers tied to `var(--wp--preset--color--{slug})`. */
function theme_color_choices(): array
{
    // 'theme' origin only — excludes WP core's generic 'default' swatches.
    $palette = wp_get_global_settings(['color', 'palette', 'theme']) ?: [];

    $choices = [];

    foreach ($palette as $color) {
        $choices[$color['slug']] = $color['name'];
    }

    return $choices;
}

/**
 * Whether a block's `$example` fallback content should show. Only on `/pattern-library`
 * (`$preview` true, `is_admin()` false); the editor canvas also has `$preview` true but
 * empty fields should read as empty, not as fixture content.
 */
function shows_example(bool $preview): bool
{
    return $preview && ! is_admin();
}

/** Whether rendered block HTML has no text and no media/interactive/nested-block elements. */
function block_output_is_empty(string $html): bool
{
    if (preg_match('/<(img|video|picture|svg|iframe|form|input|canvas|InnerBlocks|wa-[a-z-]+)\b/i', $html)) {
        return false;
    }

    return trim(wp_strip_all_tags($html)) === '';
}

/** Hex of a theme.json palette colour, or null for an unknown slug. */
function theme_color_hex(string $slug): ?string
{
    foreach (wp_get_global_settings(['color', 'palette', 'theme']) ?: [] as $color) {
        if ($color['slug'] === $slug) {
            return $color['color'];
        }
    }

    return null;
}

/** WCAG 2.x contrast ratio between two hex colours. */
function color_contrast_ratio(string $a, string $b): float
{
    $luminance = function (string $hex): float {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = preg_replace('/(.)/', '$1$1', $hex);
        }

        return array_sum(array_map(
            fn ($channel, $weight) => $weight * (($c = hexdec($channel) / 255) <= 0.03928
                ? $c / 12.92
                : (($c + 0.055) / 1.055) ** 2.4),
            str_split($hex, 2),
            [0.2126, 0.7152, 0.0722],
        ));
    };

    [$lighter, $darker] = [max($luminance($a), $luminance($b)), min($luminance($a), $luminance($b))];

    return ($lighter + 0.05) / ($darker + 0.05);
}

/** 'white' or 'blue-dark', whichever contrasts better with a palette background. */
function theme_text_color_for(string $backgroundSlug): string
{
    $background = theme_color_hex($backgroundSlug);

    if (! $background) {
        return 'white';
    }

    return color_contrast_ratio($background, theme_color_hex('white') ?? '#ffffff')
        >= color_contrast_ratio($background, theme_color_hex('blue-dark') ?? '#003852')
        ? 'white'
        : 'blue-dark';
}

/**
 * Background colours editors can pick (palette slug => label). Blocks opt in via
 * `'color' => ['background' => true]` in `$supports`; app/filters.php restricts the
 * picker to this list. Edit here to change the choices everywhere.
 */
function theme_block_background_colors(): array
{
    return [
        'light-grey' => 'Grey',
        'blue-light' => 'Blue light',
        'blue' => 'Blue',
        'blue-dark' => 'Blue dark',
    ];
}

/** CSS giving each `.has-{slug}-background-color` block a contrasting text colour; blocks follow it with `var(--c-block-fg, <default>)`. */
function theme_block_background_css(): string
{
    $css = '';

    foreach (array_keys(theme_block_background_colors()) as $slug) {
        $text = theme_text_color_for($slug);

        $css .= ".has-background.has-{$slug}-background-color{--c-block-fg:var(--wp--preset--color--{$text});color:var(--c-block-fg)}";
    }

    return $css;
}

/**
 * Main query's pagination as `<a>`/`<span>` strings for archive and search results.
 * paginate_links() carries the current query string (search term, active filters) onto each link.
 * Hidden text gives the accessible names "Page 2", "Previous page" and "Next page"; the
 * spaces outside it don't render, as the links are inline-flex.
 */
function pagination_links(): array
{
    $arrow = \Illuminate\Support\Facades\Blade::render('<x-icon name="arrow-right" />');
    $hidden = fn(string $text) => '<span class="u-sr-only">' . esc_html($text) . '</span>';

    return paginate_links([
        'type' => 'array',
        'current' => max(1, (int) get_query_var('paged')),
        'total' => (int) $GLOBALS['wp_query']->max_num_pages,
        'before_page_number' => $hidden(__('Page', 'sage')) . ' ',
        'prev_text' => $arrow . __('Previous', 'sage') . ' ' . $hidden(__('page', 'sage')),
        'next_text' => __('Next', 'sage') . ' ' . $hidden(__('page', 'sage')) . $arrow,
    ]) ?: [];
}
