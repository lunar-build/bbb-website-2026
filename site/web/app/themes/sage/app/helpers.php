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
 * Convert a WordPress nav menu theme location into the `{ columns, legal }`
 * shape expected by lunar-ui-components' `<lunar-site-footer columns="..." legal="...">`.
 *
 * Top-level items with children become a column (item label as the column
 * title, its children as the column's links); top-level items without
 * children are treated as legal links instead, since lunar-site-footer
 * only renders a column's `links` when its `children` array is non-empty.
 */
function menu_items_to_footer_columns(string $location): array
{
    $items = menu_items_to_array($location);

    $columns = [];
    $legal = [];

    foreach ($items as $item) {
        if (! empty($item['children'])) {
            $columns[] = [
                'title' => $item['label'],
                'links' => array_map(fn($child) => [
                    'label' => $child['label'],
                    'href' => $child['href'],
                ], $item['children']),
            ];
        } else {
            $legal[] = [
                'label' => $item['label'],
                'href' => $item['href'],
            ];
        }
    }

    return ['columns' => $columns, 'legal' => $legal];
}
