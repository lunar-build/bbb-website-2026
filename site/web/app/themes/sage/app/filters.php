<?php

/**
 * Theme filters.
 */

namespace App;

/**
 * Add "… Continued" to the excerpt.
 *
 * @return string
 */
add_filter('excerpt_more', function () {
    return sprintf(' &hellip; <a href="%s">%s</a>', get_permalink(), __('Continued', 'sage'));
});

/**
 * Our own ACF Composer blocks each render their own <section>/.o-container
 * wrapper (see the build-acf-block skill's "<section> root" convention),
 * but third-party/core blocks placed directly in post content — e.g. the
 * Gravity Forms block — render only their own plugin markup, with no
 * containment. Wrap specific block names here so they sit flush and
 * contained like every other block on the page.
 *
 * Add a block name to $contained to bring another non-ACF block in line.
 */
add_filter('render_block', function ($block_content, $block) {
    $contained = [
        'gravityforms/form',
    ];

    if (trim($block_content) === '' || ! in_array($block['blockName'] ?? null, $contained, true)) {
        return $block_content;
    }

    return sprintf(
        '<section class="c-block"><div class="o-container">%s</div></section>',
        $block_content,
    );
}, 10, 2);

/**
 * Warn editors on the Primary Navigation menu screen that an item with
 * children never renders as a link itself — on mobile it becomes an inert
 * heading (primary-nav.blade.php), on desktop a submenu toggle button
 * (lunar-nav) — only items with no children are actual links.
 */
add_action('admin_notices', function () {
    $screen = get_current_screen();

    if (! $screen || $screen->id !== 'nav-menus') {
        return;
    }

    global $nav_menu_selected_id;

    $primaryMenuId = get_nav_menu_locations()['primary_navigation'] ?? 0;

    if (! $primaryMenuId || $nav_menu_selected_id !== $primaryMenuId) {
        return;
    }

    echo '<div class="notice notice-warning"><p>'
        . __('Primary Navigation note: an item with sub-items does not link anywhere itself (it becomes an inert heading on mobile, a submenu toggle on desktop) — only items with no children are actual links.', 'sage')
        . '</p></div>';
});

/**
 * base/_forms.scss hides the native `input[type=checkbox]`/`input[type=radio]`
 * box (`appearance: none`) and draws a custom glyph via `::before`, which some
 * voice control software can't target on a bare pseudo-styled input. Gravity
 * Forms renders each choice's `<label>` with `id="label_…"` / `for="choice_…"`,
 * distinct from the field's own group label (which has no `for`), so this only
 * touches the individual choice labels. Note: GF renders this attribute with
 * single quotes (`for='choice_…'`), not double.
 */
add_filter('gform_field_content', function ($content, $field) {
    if (! in_array($field->type, ['checkbox', 'radio'], true)) {
        return $content;
    }

    return preg_replace('/<label\s+for=([\'"])choice_/', '<label role="link" tabindex="0" for=$1choice_', $content);
}, 10, 2);
