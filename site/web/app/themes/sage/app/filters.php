<?php

/**
 * Theme filters.
 */

namespace App;

add_filter('excerpt_more', function () {
    return sprintf(' &hellip; <a href="%s">%s</a>', get_permalink(), __('Continued', 'sage'));
});

/** "Cards" inserter category for the card blocks. */
add_filter('block_categories_all', function (array $categories) {
    return array_merge([
        [
            'slug' => 'cards',
            'title' => __('Cards', 'sage'),
        ],
    ], $categories);
});

/**
 * Force ACF block version 3. log1x/acf-composer (v3.4) hardcodes 2, which stops ACF's
 * own WP 7.1+ default applying (only used when unset). On WP 7.1 v2 blocks lose the
 * inline edit form (iframe-only canvas) and only open the Inspector; v3 restores the
 * toolbar pencil that opens a pop-out panel with every field.
 *
 * Deliberately NOT setting `auto_inline_editing` (ACF 6.7+): it only inlines
 * sole-content fields like WYSIWYG, so clients would edit the text and miss the other
 * fields. One consistent "click the pencil" path is better UX.
 */
add_filter('acf/register_block_type_args', function ($block) {
    if (! str_starts_with($block['name'] ?? '', 'acf/')) {
        return $block;
    }

    $block['acf_block_version'] = 3;

    // Empty blocks render nothing in the editor (example content is pattern-library
    // only, see shows_example()), leaving an unclickable invisible block — show a placeholder.
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
 * Blocks with a Background colour support (see theme_block_background_colors()) show that
 * control straight away in the Color panel, rather than hidden behind the panel's menu, so
 * choosing a colour is: Background, pick a swatch.
 */
add_filter('acf/register_block_type_args', function ($block) {
    if (str_starts_with($block['name'] ?? '', 'acf/') && ! empty($block['supports']['color']['background'])) {
        $block['supports']['color']['__experimentalDefaultControls'] = ['background' => true];
    }

    return $block;
});

/** Blocks allowed on pages are allowed on mec-events, without editing every $post_types. */
add_filter('acf/register_block_type_args', function ($block) {
    if (in_array('page', $block['post_types'] ?? [], true)) {
        $block['post_types'][] = 'mec-events';
    }

    return $block;
});

/**
 * MEC forces the classic editor on events, which strips our ACF blocks on save. Use the
 * block editor (MEC's panels still load as meta boxes) — priority 20, after MEC's filter.
 */
add_filter('use_block_editor_for_post_type', fn($use, $postType) => $postType === 'mec-events' ? true : $use, 20, 2);
add_filter('gutenberg_can_edit_post_type', fn($use, $postType) => $postType === 'mec-events' ? true : $use, 20, 2);

/**
 * Our ACF blocks render their own <section>/.o-container; third-party blocks (e.g.
 * Gravity Forms) don't. Add a block name to $contained to wrap it the same way.
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

/** Pre-insert Standfirst in new posts. No template_lock, so editors can still change blocks. */
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
 * Give H2s an auto-slug `id` for the sticky page menu (Gutenberg only renders one when
 * an editor sets an HTML anchor). Only on posts with a menu (PageMenu::shownFor()).
 * Must match PageMenu::anchorFor()'s fallback slug.
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

    // tabindex="-1": sticky-nav-template.js focuses it after a Page Index click (WCAG 2.4.3).
    $processor->set_attribute('tabindex', '-1');

    return $processor->get_updated_html();
}, 10, 2);

/**
 * Blocks with `'color' => ['background' => true]` in `$supports` get WP's Background
 * colour control, limited to theme_block_background_colors(). Text colour comes from
 * contrast via theme_block_background_css().
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
 * Search results' content-type filter (?filter_type[]=page) — see App\Support\SearchFilters.
 */
add_action('pre_get_posts', function (\WP_Query $query) {
    if (is_admin() || ! $query->is_main_query() || ! $query->is_search()) {
        return;
    }

    $query->set('post_type', Support\SearchFilters::queryPostTypes());
});

/**
 * Plain archive titles ("Case Studies", not "Archives: Case Studies").
 */
add_filter('get_the_archive_title_prefix', '__return_empty_string');

/**
 * URL structure. Settings → Permalinks must be "Post name" (/%postname%/), set per environment, so pages
 * and custom post types (case studies, events…) keep their own URLs. Only news belongs under /news/ — to
 * keep the live betterbybike.info URLs, posts (/news/{slug}/) and categories/tags (/news/category/…,
 * /news/tag/…) get it back below, which the Permalinks screen can't express (so its "sample-post" example
 * doesn't show /news/). A post reached at /{slug}/ redirects to its /news/ URL. Needs a rewrite flush
 * after deploy.
 */
$onPostName = fn() => get_option('permalink_structure') === '/%postname%/';

// The category/tag bases are only right without a /news/ front, so they apply only on Post name.
add_filter('pre_option_category_base', fn($pre) => $onPostName() ? 'news/category' : $pre);
add_filter('pre_option_tag_base', fn($pre) => $onPostName() ? 'news/tag' : $pre);

add_action('admin_notices', function () use ($onPostName) {
    if ($onPostName() || ! current_user_can('manage_options')) {
        return;
    }

    printf(
        '<div class="notice notice-warning"><p>%s</p></div>',
        wp_kses_post(__('Set <a href="options-permalink.php">Settings → Permalinks</a> to <strong>Post name</strong>. News URLs (/news/…) depend on it.', 'sage')),
    );
});

// Core builds a post's URL from the structure alone, so this is what puts posts under /news/.
add_filter('pre_post_link', fn($permalink, $post) => $post->post_type === 'post' ? '/news/%postname%/' : $permalink, 10, 2);

add_action('init', function () {
    // The rules that resolve /news/{slug}/ (and its /page/N, /feed and embed endpoints) to the post.
    add_permastruct('news_post', 'news/%postname%/', ['with_front' => false, 'ep_mask' => EP_PERMALINK]);

    // The post rule above would otherwise take /news/feed/ and /news/page/2/ for posts named "feed" and "page".
    add_rewrite_rule('^news/feed/(feed|rdf|rss|rss2|atom)/?$', 'index.php?feed=$matches[1]', 'top');
    add_rewrite_rule('^news/(feed|rdf|rss|rss2|atom)/?$', 'index.php?feed=$matches[1]', 'top');
    add_rewrite_rule('^news/page/([0-9]{1,})/?$', 'index.php?pagename=news&paged=$matches[1]', 'top');

    // MEC's events/[^/]+/[^/]+ attachment rule would otherwise take /events/category/{slug}/.
    add_rewrite_rule('^events/category/([^/]+)/(feed|rdf|rss|rss2|atom)/?$', 'index.php?mec_category=$matches[1]&feed=$matches[2]', 'top');
    add_rewrite_rule('^events/category/([^/]+)/page/([0-9]{1,})/?$', 'index.php?mec_category=$matches[1]&paged=$matches[2]', 'top');
    add_rewrite_rule('^events/category/([^/]+)/?$', 'index.php?mec_category=$matches[1]', 'top');
});

// The structure also resolves a post at /{slug}/; send it to its /news/ URL (core doesn't).
add_action('template_redirect', function () {
    if (! is_singular('post') || is_preview()) {
        return;
    }

    $request = trim((string) wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');

    if (! str_starts_with($request, 'news/')) {
        wp_safe_redirect(get_permalink(), 301);
        exit;
    }
}, 9);

/**
 * Custom post types and taxonomies keep their own root slugs. The plain /%postname%/ structure has no
 * /news/ front to inherit, so with_front => false is just a guard against one being added later.
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

// Event categories are /events/category/{slug}/ on the live site (MEC's default is /mec-category/).
add_filter('register_taxonomy_args', function (array $args, string $name) {
    if ($name !== 'mec_category') {
        return $args;
    }

    return array_replace($args, ['rewrite' => array_replace(is_array($args['rewrite'] ?? null) ? $args['rewrite'] : [], [
        'slug' => 'events/category',
        'with_front' => false,
    ])]);
}, 11, 2);

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

/**
 * Marker.io feedback widget → Trello. Dev and staging only, never production.
 * The project ID is public by design (Marker proxies the onward Trello credentials).
 */
add_action('wp_head', function () {
    if (! in_array(WP_ENV, ['development', 'staging'], true)) {
        return;
    }

    echo '<script>window.markerProject=' . wp_json_encode(env('MARKER_PROJECT_ID') ?: '6adf384284936c1d0de1c19e') . ';</script>';
});
