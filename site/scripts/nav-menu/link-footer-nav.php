<?php

/**
 * Rebuilds the Footer menu (slug `footer`) to match production: About, Contact,
 * Terms & Conditions, Accessibility and Privacy, in that order, as page links.
 * The test menu it replaces (Group 1/2/3 with custom /terms/ and /privacy/ links)
 * pointed at URLs that don't exist.
 *
 * The footer template wants three top-level parents it never renders, with the
 * visible links as their children (see menu_items_to_footer_columns()), so the
 * five links are spread across three columns in production's order.
 *
 * Dry run unless the first argument is `apply`. From /site:
 *   ddev wp eval-file scripts/nav-menu/link-footer-nav.php
 *   ddev wp eval-file scripts/nav-menu/link-footer-nav.php apply
 */

$apply = ($args[0] ?? '') === 'apply';
$menu = wp_get_nav_menu_object('footer');

if (! $menu) {
    WP_CLI::error('No menu with slug "footer".');
}

// page slug => label ('' keeps the page title; production labels the Privacy Policy page "Privacy")
$columns = [
    ['about' => '', 'contact' => ''],
    ['terms-conditions' => '', 'accessibility' => ''],
    ['privacy-policy' => 'Privacy'],
];

$plan = [];

foreach ($columns as $n => $links) {
    foreach ($links as $slug => $label) {
        $page = get_page_by_path($slug);

        if (! $page || $page->post_status !== 'publish') {
            WP_CLI::error("No published page with slug \"{$slug}\".");
        }

        $plan[$n][] = [$page, $label];
    }
}

$existing = wp_get_nav_menu_items($menu->term_id, ['post_status' => 'any']) ?: [];

WP_CLI::log(count($existing) . ' existing item(s) will be replaced by:');

foreach ($plan as $n => $links) {
    WP_CLI::log('  column ' . ($n + 1));

    foreach ($links as [$page, $label]) {
        WP_CLI::log(sprintf('    %-20s → page #%d %s', $label ?: $page->post_title, $page->ID, get_permalink($page)));
    }
}

if (! $apply) {
    WP_CLI::success('Dry run; pass `apply` to write.');

    return;
}

foreach ($existing as $item) {
    wp_delete_post($item->ID, true);
}

$position = 0;

foreach ($plan as $n => $links) {
    $parent = wp_update_nav_menu_item($menu->term_id, 0, [
        'menu-item-title' => 'Column ' . ($n + 1),
        'menu-item-url' => '#',
        'menu-item-type' => 'custom',
        'menu-item-position' => ++$position,
        'menu-item-status' => 'publish',
    ]);

    foreach ($links as [$page, $label]) {
        wp_update_nav_menu_item($menu->term_id, 0, array_filter([
            'menu-item-title' => $label,
            'menu-item-type' => 'post_type',
            'menu-item-object' => 'page',
            'menu-item-object-id' => $page->ID,
            'menu-item-parent-id' => $parent,
            'menu-item-position' => ++$position,
            'menu-item-status' => 'publish',
        ], fn($v) => $v !== ''));
    }
}

WP_CLI::success('Footer menu rebuilt.');
