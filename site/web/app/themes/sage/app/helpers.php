<?php

/**
 * Convert a WordPress nav menu theme location into a nested item array:
 * [{ label, href, current, children }, ...], children nested recursively.
 * Consumed by sections/header.blade.php and sections/primary-nav.blade.php.
 */
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
            'label' => $item->title,
            'href' => $item->url,
            'current' => (bool) $item->current,
            'children' => $build((int) $item->ID),
        ], $by_parent[$parent_id] ?? []);
    };

    return $build(0);
}

/**
 * Convert a WordPress nav menu theme location into a `columns` shape: one
 * column per top-level item (item label as the column title, its children
 * as the column's links). Consumed by sections/footer.blade.php.
 */
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

/**
 * Convert the Theme Options "Legal Links" repeater into a `{ label, href }[]`
 * list. Sourced independently from the footer nav menu (not derived from
 * it), since not every project using this theme will want legal links to
 * be whichever footer menu items happen to have no children.
 */
function legal_links_from_options(): array
{
    $rows = get_field('legal_links', 'option') ?: [];

    return array_map(fn($row) => [
        'label' => $row['label'],
        'href' => $row['href'],
    ], $rows);
}

/**
 * Convert the Theme Options "Social" tab's fixed per-platform URL fields
 * into a `{ platform, url }[]` list, skipping any platform left blank.
 * `platform` is the field's own slug (e.g. 'facebook', 'x') rather than a
 * free-text label, so it can be matched to an icon deterministically —
 * see app/Options/ThemeOptions.php for why these are individual fields
 * rather than a repeater.
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
 * Coerce an ACF `link` field value to its expected shape — ACF normally
 * returns an array, but a legacy/incomplete row can store a plain string
 * (or nothing at all), which would fatal on array access. Shared by any
 * block with a repeater of link-bearing rows (see CardRow.php's own
 * near-identical normalizeLink(), predating this helper).
 */
function normalize_link($link): array
{
    if (is_array($link)) {
        return $link + ['title' => '', 'url' => '', 'target' => ''];
    }

    return ['title' => '', 'url' => (string) ($link ?? ''), 'target' => ''];
}

/*
 * Build an ACF `choices`-shaped array (`['slug' => 'Human Label']`) from
 * every SVG in resources/svg/ — the curated icon set used by `<x-icon
 * name="...">` (see resources/views/components/icon.blade.php). Glob-based
 * so the icon picker on any ACF select field using this grows automatically
 * as new SVGs are added to that folder, with no code change needed here.
 */
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

/**
 * Build an ACF `choices`-shaped array (`['slug' => 'Human Label']`) from
 * the theme's global colour palette (`theme.json`'s `color.palette`, via
 * WP's own settings API so it reflects any core/plugin overrides too) —
 * for ACF select fields offering a background colour pick tied to
 * `var(--wp--preset--color--{slug})`, e.g. IconLinkGrid's `background_color`.
 */
function theme_color_choices(): array
{
    // wp_get_global_settings(['color', 'palette']) groups by origin
    // (theme/default/custom) — 'theme' is specifically this theme.json's
    // own brand palette; WP core's generic 'default' swatches (vivid-red,
    // pale-pink, etc.) aren't wanted as background choices here.
    $palette = wp_get_global_settings(['color', 'palette', 'theme']) ?: [];

    $choices = [];

    foreach ($palette as $color) {
        $choices[$color['slug']] = $color['name'];
    }

    return $choices;
}
