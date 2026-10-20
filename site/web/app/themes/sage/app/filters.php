<?php

/**
 * Theme filters.
 */

namespace App;

add_filter('excerpt_more', function () {
    return sprintf(' &hellip; <a href="%s">%s</a>', get_permalink(), __('Continued', 'sage'));
});

/**
 * Register a "Cards" block category so the card blocks (Card Row, Cycle
 * Route Card, Feature Card, Business Card, Image Card) group together
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
 * log1x/acf-composer (v3.4) hardcodes every block's `acf_block_version` to 2
 * (see Block::$blockVersion), which stops ACF 6.8.10's own WP 7.1+ default
 * (version_compare($wp_version, '7.1', '>=') ? 3 : 2) from ever kicking in —
 * ACF only applies that default when `acf_block_version` is unset. Blocks
 * stuck on v2 lose the old "auto" mode's inline edit-form swap entirely on
 * WP 7.1 (its canvas is iframe-only now, and ACF's v2 form injection isn't
 * iframe-compatible), so clicking a block only ever opens the Inspector
 * sidebar. Force v3 here, which also brings back the block toolbar's pencil
 * icon ("expanded editor") — click it to open a full-size pop-out panel with
 * every field, the same one access point for every block regardless of
 * field type.
 *
 * Deliberately NOT setting `auto_inline_editing` (ACF 6.7+, makes any field
 * whose value is the sole content of an element — e.g. WYSIWYG text —
 * editable directly in the canvas, like core InnerBlocks). It looks like it
 * fixes the sidebar problem, but it only applies to that one field type,
 * so clients can edit the text inline and assume that's the whole block,
 * missing the rest of the fields (icon, image, layout options, etc.) which
 * still need the pop-out editor. Two different edit paths depending on
 * field type is worse UX than one consistent "click the pencil" path.
 */
add_filter('acf/register_block_type_args', function ($block) {
    if (! str_starts_with($block['name'] ?? '', 'acf/')) {
        return $block;
    }

    $block['acf_block_version'] = 3;

    // Empty blocks render as nothing in the editor now that example content
    // is pattern-library only (see shows_example()), leaving a client with
    // an invisible block they can't find or click — show a placeholder.
    if (is_callable($block['render_callback'] ?? null)) {
        $render = $block['render_callback'];

        $block['render_callback'] = function ($attrs, ...$args) use ($render) {
            ob_start();
            $render($attrs, ...$args);
            $html = ob_get_clean();

            $is_preview = $args[1] ?? false;

            if (! $is_preview || ! is_admin() || ! block_output_is_empty($html)) {
                echo $html;

                return;
            }

            printf(
                '<section class="c-block-placeholder">%s</section>',
                esc_html(sprintf(
                    /* translators: %s: block title */
                    __('%s — click the pencil to add content.', 'sage'),
                    $attrs['title'] ?? __('Block', 'sage'),
                )),
            );
        };
    }

    return $block;
});

/**
 * Any block allowed on pages is allowed on events (mec-events) — one filter
 * rather than adding 'mec-events' to every block class's $post_types.
 */
add_filter('acf/register_block_type_args', function ($block) {
    if (in_array('page', $block['post_types'] ?? [], true)) {
        $block['post_types'][] = 'mec-events';
    }

    return $block;
});

/**
 * MEC forces the classic editor on events, which can't show our ACF blocks
 * and strips them on save. Use the block editor (MEC's panels still load as
 * meta boxes) — priority 20, after MEC's filter.
 */
add_filter('use_block_editor_for_post_type', fn($use, $postType) => $postType === 'mec-events' ? true : $use, 20, 2);
add_filter('gutenberg_can_edit_post_type', fn($use, $postType) => $postType === 'mec-events' ? true : $use, 20, 2);

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
 * Pre-insert the Standfirst block at the top of every new post's content,
 * so it's visible/editable in the canvas immediately. No template_lock —
 * editors can still freely add/remove/reorder blocks after.
 */
add_action('init', function () {
    $post_type_object = get_post_type_object('post');

    if ($post_type_object) {
        $post_type_object->template = [
            ['acf/standfirst'],
        ];
    }
}, 20);

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
    // The form's own button text: GF 3 renders <button>Text</button>, older
    // markup <input value='Text'> (single-quoted).
    $label = preg_match('#<button[^>]*>(.*?)</button>#s', $button, $matches)
        ? wp_strip_all_tags($matches[1])
        : (preg_match('/value=([\'"])(.*?)\1/', $button, $matches) ? $matches[2] : '');

    return view('partials.gf-submit-button', [
        'id' => 'gform_submit_button_' . $form['id'],
        'label' => trim(html_entity_decode($label, ENT_QUOTES)) ?: __('Submit', 'sage'),
    ])->render();
}, 10, 2);

/**
 * The sticky page menu links to each H2 by `id`. Gutenberg only renders an
 * `id` when an editor sets the heading's "HTML anchor", so give the rest an
 * auto-slug. Only for posts whose menu was built
 * (App\Support\PageMenu::shownFor()), so other pages' H2s are untouched.
 *
 * The slugify logic here must match App\Support\PageMenu::anchorFor()'s
 * fallback, which builds the same `items` array of links.
 */
add_filter('render_block_core/heading', function ($block_content, $block) {
    if ((int) ($block['attrs']['level'] ?? 2) !== 2) {
        return $block_content;
    }

    if (! Support\PageMenu::shownFor((int) get_the_ID())) {
        return $block_content;
    }

    $processor = new \WP_HTML_Tag_Processor($block_content);

    if (! $processor->next_tag('h2')) {
        return $block_content;
    }

    if (empty($block['attrs']['anchor'])) {
        $anchor = Support\PageMenu::slugify(wp_strip_all_tags($block_content));

        if ($anchor !== '') {
            $processor->set_attribute('id', $anchor);
        }
    }

    // Focusable without joining the natural tab order — resources/js/sticky-nav-template.js
    // moves focus here after a Page Index click, so keyboard/screen-reader users land in the
    // jumped-to section instead of staying on the nav link (WCAG 2.4.3 Focus Order).
    $processor->set_attribute('tabindex', '-1');

    return $processor->get_updated_html();
}, 10, 2);

/**
 * Block background colours.
 *
 * Any ACF block that sets `'color' => ['background' => true]` in its
 * `$supports` gets WP's native Background colour control (in the sidebar's
 * Styles tab, alongside padding) — limited here to
 * theme_block_background_colors() rather than the whole theme palette. The
 * text colour is set from contrast by theme_block_background_css().
 */
add_filter('wp_theme_json_data_theme', function ($theme_json) {
    $data = $theme_json->get_data();
    $palette = collect($data['settings']['color']['palette']['theme'] ?? [])
        ->filter(fn($color) => array_key_exists($color['slug'], theme_block_background_colors()))
        ->values()
        ->all();

    $blocks = [];

    foreach (\WP_Block_Type_Registry::get_instance()->get_all_registered() as $name => $type) {
        if (str_starts_with($name, 'acf/') && ! empty($type->supports['color']['background'])) {
            $blocks[$name] = ['color' => ['palette' => ['theme' => $palette]]];
        }
    }

    return $blocks
        ? $theme_json->update_with(['version' => $data['version'] ?? 3, 'settings' => ['blocks' => $blocks]])
        : $theme_json;
});

add_action('wp_head', function () {
    echo '<style id="theme-block-backgrounds">' . theme_block_background_css() . '</style>';
});

add_filter('block_editor_settings_all', function ($settings) {
    $settings['styles'][] = ['css' => theme_block_background_css()];

    return $settings;
});

/**
 * Apply the archive template's taxonomy filters (?filter_{taxonomy}[]=slug)
 * to the main archive query — see App\Support\ArchiveFilters.
 */
add_action('pre_get_posts', function (\WP_Query $query) {
    if (is_admin() || ! $query->is_main_query()) {
        return;
    }

    if (! ($query->is_home() || $query->is_post_type_archive() || $query->is_category() || $query->is_tax())) {
        return;
    }

    $postType = Support\ArchiveFilters::postType();

    if (! $taxQuery = Support\ArchiveFilters::taxQuery($postType)) {
        return;
    }

    $query->set('tax_query', $taxQuery);
    $query->set('ignore_sticky_posts', true);
});

/**
 * Plain archive titles ("Case Studies", not "Archives: Case Studies").
 */
add_filter('get_the_archive_title_prefix', '__return_empty_string');

/**
 * Posts use a /news/%postname%/ permalink structure, whose /news/ "front"
 * WordPress otherwise prefixes onto every post type and taxonomy. Only news
 * (posts, categories, tags) belongs under /news/ — default everything else,
 * including plugin-registered types like events, to its own root slug.
 */
$withoutFront = function (array $args, string $name) {
    $builtin = in_array($name, ['post', 'page', 'attachment', 'category', 'post_tag', 'post_format'], true);

    if ($builtin || ($args['rewrite'] ?? true) === false) {
        return $args;
    }

    $rewrite = is_array($args['rewrite'] ?? null) ? $args['rewrite'] : [];
    $args['rewrite'] = $rewrite + ['with_front' => false];

    return $args;
};

add_filter('register_post_type_args', $withoutFront, 10, 2);
add_filter('register_taxonomy_args', $withoutFront, 10, 2);

/**
 * Cycling Centre sessions are MEC events, but the live site has them at
 * /cycling-centres/bristol-cycling-centre/whats-on/{slug}/ rather than MEC's
 * /events/{slug}/. Keep that URL for events in the Bristol Cycling Centre
 * category so no redirects are needed. Needs a permalink flush after deploy.
 */
const CYCLING_CENTRE_EVENTS_PATH = 'cycling-centres/bristol-cycling-centre/whats-on';
const CYCLING_CENTRE_EVENTS_CATEGORY = 'bristol-cycling-centre';

add_action('init', function () {
    add_rewrite_rule(
        '^' . CYCLING_CENTRE_EVENTS_PATH . '/([^/]+)/?$',
        'index.php?post_type=mec-events&name=$matches[1]',
        'top',
    );
});

add_filter('post_type_link', function (string $link, \WP_Post $post) {
    if ($post->post_type !== 'mec-events' || ! has_term(CYCLING_CENTRE_EVENTS_CATEGORY, 'mec_category', $post)) {
        return $link;
    }

    return home_url(user_trailingslashit(CYCLING_CENTRE_EVENTS_PATH . '/' . $post->post_name));
}, 10, 2);

/**
 * MEC swaps in its own templates on template_include (99) unless the theme
 * has a plain `single-mec-events.php`, which a Blade theme never has. Redo
 * WordPress's lookup so Sage finds our Blade views before Acorn renders at
 * 100. Same priority as MEC's, registered later, so it runs after it.
 */
add_filter('template_include', function (string $template) {
    if (is_singular('mec-events')) {
        return get_single_template() ?: $template;
    }
    if (is_post_type_archive('mec-events')) {
        return get_post_type_archive_template() ?: $template;
    }

    return $template;
}, 99);

/**
 * Multi-line Cost box in MEC's event editor, one price per line. Saved in
 * the `|`-separated format SingleMecEvent::priceLines() splits into bullets;
 * keeps the #mec_cost id that MEC's "This event is free" script targets.
 */
add_filter('mec_event_cost_custom_field_status', '__return_true');

add_action('mec_event_cost_custom_field', function ($cost, $type, $name) {
    $lines = array_filter(array_map('trim', explode('|', (string) $cost)), 'strlen');
    printf(
        '<textarea name="%s" id="mec_cost" rows="4" class="large-text" aria-describedby="mec_cost_lines_help">%s</textarea>'
        . '<p class="mec-field__help" id="mec_cost_lines_help">%s</p>',
        esc_attr($name),
        esc_textarea(implode("\n", $lines)),
        esc_html__('One price per line — each line shows as its own bullet, e.g. "£5 per child" then "£7 per solo adult". A plain number like 5 shows as £5.', 'sage'),
    );
}, 10, 3);

// MEC's save runs the cost through sanitize_text_field(), which flattens line
// breaks — join the lines with `|` first (save_post_{type} fires before
// MEC's save_post handler).
add_action('save_post_mec-events', function () {
    if (! isset($_POST['mec']['cost']) || ! is_string($_POST['mec']['cost'])) {
        return;
    }
    $lines = preg_split('/\R/', wp_unslash($_POST['mec']['cost']));
    $_POST['mec']['cost'] = wp_slash(implode(' | ', array_filter(array_map('trim', $lines), 'strlen')));
}, 9);

/**
 * "Page options" box on the event edit screen: untick to hide the "More
 * upcoming sessions" list on that class's page (shown by default).
 */
add_action('add_meta_boxes_mec-events', function () {
    add_meta_box('bbb-event-page-options', __('Page options', 'sage'), function (\WP_Post $post) {
        wp_nonce_field('bbb_event_page_options', 'bbb_event_page_options_nonce');
        printf(
            '<label><input type="checkbox" name="bbb_show_upcoming" value="1"%s> %s</label>',
            checked(! get_post_meta($post->ID, 'bbb_hide_upcoming', true), true, false),
            esc_html__('Show "More upcoming sessions" on the page', 'sage'),
        );
    }, 'mec-events', 'side');
});

add_action('save_post_mec-events', function (int $postId) {
    if (! isset($_POST['bbb_event_page_options_nonce']) || ! wp_verify_nonce(sanitize_key($_POST['bbb_event_page_options_nonce']), 'bbb_event_page_options') || ! current_user_can('edit_post', $postId)) {
        return;
    }
    empty($_POST['bbb_show_upcoming']) ? update_post_meta($postId, 'bbb_hide_upcoming', 1) : delete_post_meta($postId, 'bbb_hide_upcoming');
});

/**
 * The /events/ archive uses the theme's archive view (no design of its own
 * yet). MEC's query parsing forces 1 post per page there; restore the site
 * setting, and list events by their next session, undated ones last.
 */
add_action('pre_get_posts', function (\WP_Query $query) {
    if (is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive('mec-events')) {
        return;
    }

    $query->set('posts_per_page', (int) get_option('posts_per_page'));

    // MEC's "Hide from calendars & lists" visibility (mec_public = 0).
    $query->set('meta_query', [[
        'relation' => 'OR',
        ['key' => 'mec_public', 'compare' => 'NOT EXISTS'],
        ['key' => 'mec_public', 'value' => '0', 'compare' => '!='],
    ]]);

    add_filter('posts_clauses', function (array $clauses, \WP_Query $q) use ($query) {
        if ($q !== $query) {
            return $clauses;
        }
        global $wpdb;

        // MEC stores wall-clock times as UTC timestamps; see App\Support\EventSessions.
        $now = strtotime(current_time('Y-m-d H:i:s') . ' UTC');
        $clauses['join'] .= $wpdb->prepare(
            " LEFT JOIN (SELECT post_id, MIN(tstart) AS next_session FROM {$wpdb->prefix}mec_dates WHERE tstart >= %d GROUP BY post_id) mec_next ON mec_next.post_id = {$wpdb->posts}.ID",
            $now,
        );
        $clauses['orderby'] = "mec_next.next_session IS NULL, mec_next.next_session ASC, {$wpdb->posts}.post_title ASC";

        return $clauses;
    }, 10, 2);
}, 20);

/**
 * Hide MEC's "Hourly Schedule" tab on the event screen: class times come from
 * event timetables, and nothing on the front end shows an hourly schedule.
 */
add_filter('mec-single-event-meta-title', function ($tabs) {
    unset($tabs[__('Hourly Schedule', 'mec')]);

    return $tabs;
});
