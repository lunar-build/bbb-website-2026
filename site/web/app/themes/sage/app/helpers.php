<?php

/**
 * Convert a WordPress nav menu theme location into the nested item-array
 * shape expected by lunar-ui-components' `<lunar-nav items="...">`:
 * [{ label, href, current, children }, ...], children nested recursively.
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
 * Convert a WordPress nav menu theme location into the `columns` shape
 * expected by lunar-ui-components' `<lunar-site-footer columns="...">`:
 * one column per top-level item (item label as the column title, its
 * children as the column's links).
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
 * Convert the Theme Options "Legal Links" repeater into the `legal` shape
 * expected by lunar-ui-components' `<lunar-site-footer legal="...">`.
 * Sourced independently from the footer nav menu (not derived from it),
 * since not every project using this theme will want legal links to be
 * whichever footer menu items happen to have no children.
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
