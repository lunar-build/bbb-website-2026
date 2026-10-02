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
 * Register a "Cards" block category so the card blocks (Card Row, Cycle
 * Route Card, Feature Card, Filter Result Card, Image Card) group together
 * in the inserter instead of sitting loose under "Text".
 */
add_filter('block_categories_all', function (array $categories) {
    return array_merge([
        [
            'slug' => 'cards',
            'title' => __('Cards', 'sage'),
        ],
    ], $categories);
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
 * heading (primary-nav.blade.php), on desktop a submenu toggle button —
 * only items with no children are actual links.
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

/**
 * Replace Gravity Forms' plain `<input type="submit">` with our brand
 * `<wa-button>` (yellow pill + arrow) so form CTAs match every other CTA.
 */
add_filter('gform_submit_button', function ($button, $form) {
    preg_match('/value="([^"]*)"/', $button, $matches);

    return view('partials.gf-submit-button', [
        'id' => 'gform_submit_button_'.$form['id'],
        'label' => $matches[1] ?? __('Submit', 'sage'),
    ])->render();
}, 10, 2);

/**
 * The Sticky Nav page template (template-sticky-nav.blade.php) builds its
 * left-rail page menu from each top-level (H2) heading, linking to it by
 * `id`. Gutenberg already renders a heading's `id` automatically when an
 * editor sets its "HTML anchor" field — this only covers the headings
 * where they haven't, auto-slugging the heading text instead so the
 * generated link actually resolves. Scoped to the one template so other
 * pages' H2s are untouched.
 *
 * The slugify logic here must match App\Support\PageMenu::anchorFor()'s
 * fallback, which builds the same `items` array of links.
 */
add_filter('render_block_core/heading', function ($block_content, $block) {
    if ((int) ($block['attrs']['level'] ?? 2) !== 2 || ! empty($block['attrs']['anchor'])) {
        return $block_content;
    }

    if (! is_page_template('template-sticky-nav.blade.php')) {
        return $block_content;
    }

    $anchor = Support\PageMenu::slugify(wp_strip_all_tags($block_content));

    if ($anchor === '') {
        return $block_content;
    }

    $processor = new \WP_HTML_Tag_Processor($block_content);

    if (! $processor->next_tag('h2')) {
        return $block_content;
    }

    $processor->set_attribute('id', $anchor);

    return $processor->get_updated_html();
}, 10, 2);
