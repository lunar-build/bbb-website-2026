<?php

/**
 * Plugin Name: MEC Lunar Theme
 * Description: Restyles Modern Events Calendar to the Better by Bike theme: its colours, calendar CSS and JS, weekday labels and cancelled/postponed titles. Deactivate to get stock MEC back.
 * Version: 1.0.0
 */

if (! defined('ABSPATH')) {
    exit;
}

/*
 * Colours MEC's generated CSS (.mec-color, buttons, dots) follows; mirrors the theme palette. Filtered on
 * read, so nothing is saved to the database and deactivating restores whatever MEC has stored.
 */
add_filter('option_mec_options', function ($options) {
    if (is_array($options)) {
        $options['styling'] = array_merge($options['styling'] ?? [], [
            'color' => '#1878A5', // blue
            'title_color' => '#363636', // charcoal
            'title_color_hover' => '#1878A5',
            'content_color' => '#363636',
            'primary_border_radius' => '20',
            'secondary_border_radius' => '8',
        ]);
    }

    return $options;
});

/*
 * Events without a colour get the theme blue, so every event has a dot on the calendar. Applied when MEC
 * reads the meta (the one key, or all of it as its own loader does), so events saved before this still get
 * it and no data changes.
 */
function mec_lunar_default_event_color($value, $postId, $metaKey, $single)
{
    if ($value !== null || ($metaKey !== 'mec_color' && $metaKey !== '') || get_post_type($postId) !== 'mec-events') {
        return $value;
    }

    remove_filter('get_post_metadata', __FUNCTION__, 10);
    $stored = get_post_meta($postId, $metaKey, $single);
    add_filter('get_post_metadata', __FUNCTION__, 10, 4);

    $defaultColor = '1878A5';

    if ($metaKey === 'mec_color') {
        return trim((string) $stored) === '' ? ($single ? $defaultColor : [$defaultColor]) : $value;
    }

    if (is_array($stored) && trim((string) ($stored['mec_color'][0] ?? '')) === '') {
        $stored['mec_color'] = [$defaultColor];

        // WordPress unwraps a single-value filter result with [0].
        return $single ? [$stored] : $stored;
    }

    return $value;
}
add_filter('get_post_metadata', 'mec_lunar_default_event_color', 10, 4);

/*
 * MEC's calendars and lists show a cancelled or postponed event like any other (only the single page says
 * so). Say it in the title; this filter is the one place all its skins get a title from.
 */
add_filter('mec_occurrence_event_title', function ($title, $event) {
    $status = $event->data->meta['mec_event_status'] ?? '';
    $label = [
        'EventCancelled' => __('Cancelled', 'mec-lunar-theme'),
        'EventPostponed' => __('Postponed', 'mec-lunar-theme'),
    ][$status] ?? null;

    return $label ? sprintf('%s (%s)', $title, $label) : $title;
}, 10, 2);

/*
 * MEC's calendar weekday headings are two letters (MO, TU…). Show three (Mon, Tue…), as translatable strings.
 * MEC passes them already in the order the week starts, so map each by its two letters.
 */
add_filter('mec_weekday_abbr_labels', function (array $labels) {
    $threeLetter = [
        'MO' => _x('Mon', 'calendar weekday heading', 'mec-lunar-theme'),
        'TU' => _x('Tue', 'calendar weekday heading', 'mec-lunar-theme'),
        'WE' => _x('Wed', 'calendar weekday heading', 'mec-lunar-theme'),
        'TH' => _x('Thu', 'calendar weekday heading', 'mec-lunar-theme'),
        'FR' => _x('Fri', 'calendar weekday heading', 'mec-lunar-theme'),
        'SA' => _x('Sat', 'calendar weekday heading', 'mec-lunar-theme'),
        'SU' => _x('Sun', 'calendar weekday heading', 'mec-lunar-theme'),
    ];

    return array_map(fn($label) => $threeLetter[strtoupper($label)] ?? $label, $labels);
});

/*
 * The date picker in MEC's event editor (Date And Time) is jQuery UI's. MEC draws each day as a circle, but
 * jQuery UI right-aligns the number inside its link, so it sits off-centre in the circle. The ID selector is
 * needed to out-rank MEC's own rules.
 */
add_action('admin_enqueue_scripts', function () {
    wp_add_inline_style('mec-backend-style', '#ui-datepicker-div.ui-datepicker .ui-datepicker-calendar td a, #ui-datepicker-div.ui-datepicker .ui-datepicker-calendar td span { padding: 0; text-align: center; }');
}, 20);

/*
 * Front end: our CSS and JS, only where MEC's own assets are loaded. The theme drops MEC's assets from pages
 * with no calendar (priority 100), so MEC's main stylesheet still being queued after that means a calendar is
 * here. The assets are built by the theme's Vite (resources/js/mec-lunar-theme.js), so this does nothing
 * under a theme without it.
 */
function mec_lunar_page_has_calendar(): bool
{
    return wp_style_is('mec-frontend-style', 'enqueued');
}

/*
 * The few words our styling adds (filter lead-ins in CSS, the empty-month line in JS), as translatable
 * strings: CSS variables for the stylesheet, a global for the script.
 */
add_action('wp_head', function () {
    if (! mec_lunar_page_has_calendar()) {
        return;
    }

    $words = [
        'category' => __('Show events for:', 'mec-lunar-theme'),
        'location' => __('Where:', 'mec-lunar-theme'),
        'organizer' => __('Run by:', 'mec-lunar-theme'),
        'speaker' => __('Speaker:', 'mec-lunar-theme'),
        'label' => __('Label:', 'mec-lunar-theme'),
        'tag' => __('Tag:', 'mec-lunar-theme'),
    ];
    $variables = '';
    foreach ($words as $name => $text) {
        $variables .= sprintf('--mec-text-%s:%s;', $name, wp_json_encode($text, JSON_UNESCAPED_UNICODE));
    }

    printf("<style id=\"mec-text\">:root{%s}</style>\n", $variables);
    printf("<script>window.bbbMec=%s;</script>\n", wp_json_encode(['emptyMonth' => __('No events this month.', 'mec-lunar-theme')]));

    if (class_exists(\Illuminate\Support\Facades\Vite::class)) {
        echo \Illuminate\Support\Facades\Vite::withEntryPoints(['resources/js/mec-lunar-theme.js'])->toHtml();
    }
}, 101);
