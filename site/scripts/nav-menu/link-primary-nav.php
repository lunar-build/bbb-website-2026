<?php

/**
 * Points Primary Navigation at the migrated old-site "Navigation" menu (slug
 * `navigation`, 69 items, already linked to pages) instead of the hand-built
 * test menu of `#` links, and tidies that menu's links so they work on any host:
 *
 * - custom links to betterbybike.info become relative (`/shops-and-repair/…`),
 *   so staging and production don't send visitors to the live site;
 * - those with no query string or fragment that match a published page become
 *   real page links, so they follow the page if its slug changes.
 *
 * Dry run unless the first argument is `apply`. From /site:
 *   ddev wp eval-file scripts/nav-menu/link-primary-nav.php
 *   ddev wp eval-file scripts/nav-menu/link-primary-nav.php apply
 */

$apply = ($args[0] ?? '') === 'apply';
$menu = wp_get_nav_menu_object('navigation');

if (! $menu) {
    WP_CLI::error('No menu with slug "navigation".');
}

$liveHosts = ['betterbybike.info', 'www.betterbybike.info'];
$changes = [];

foreach (wp_get_nav_menu_items($menu->term_id, ['post_status' => 'any']) as $item) {
    if ($item->type !== 'custom' || ! in_array(wp_parse_url($item->url, PHP_URL_HOST), $liveHosts, true)) {
        continue;
    }

    $parts = wp_parse_url($item->url);
    $path = $parts['path'] ?? '/';
    $relative = $path . (isset($parts['query']) ? '?' . $parts['query'] : '') . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
    $page = isset($parts['query']) || isset($parts['fragment']) ? null : get_page_by_path(trim($path, '/'));

    if ($page && $page->post_status === 'publish') {
        $changes[] = [$item->ID, $item->title, "page #{$page->ID} {$path}"];

        if ($apply) {
            update_post_meta($item->ID, '_menu_item_type', 'post_type');
            update_post_meta($item->ID, '_menu_item_object', 'page');
            update_post_meta($item->ID, '_menu_item_object_id', $page->ID);
            update_post_meta($item->ID, '_menu_item_url', '');
        }

        continue;
    }

    $changes[] = [$item->ID, $item->title, "custom {$relative}"];

    if ($apply) {
        update_post_meta($item->ID, '_menu_item_url', $relative);
    }
}

foreach ($changes as [$id, $title, $to]) {
    WP_CLI::log(sprintf('%d  %-40s → %s', $id, $title, $to));
}

$locations = get_nav_menu_locations();
$current = $locations['primary_navigation'] ?? 0;

WP_CLI::log(sprintf('primary_navigation: menu %d → %d', $current, $menu->term_id));

if (! $apply) {
    WP_CLI::success(count($changes) . ' link(s) to change. Dry run; pass `apply` to write.');

    return;
}

set_theme_mod('nav_menu_locations', ['primary_navigation' => $menu->term_id] + $locations);
WP_CLI::success(count($changes) . ' link(s) updated; Primary Navigation now uses "' . $menu->name . '".');
