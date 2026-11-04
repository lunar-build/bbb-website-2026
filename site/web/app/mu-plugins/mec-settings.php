<?php

/**
 * Plugin Name: Modern Events Calendar settings
 * Description: Settings this site relies on, fixed in code so every environment matches. An mu-plugin because MEC reads and caches its settings while plugins load — before the theme's filters exist.
 */

/*
 * Text ("Alphabetic") cost type: the default number input loads our text
 * prices ("£5 per child | £7 per adult") as empty, so saving wiped them.
 */
add_filter('option_mec_options', function ($options) {
    if (is_array($options)) {
        $options['settings']['single_cost_type'] = 'alphabetic';

        // UK date (day first) and 24-hour time in the event editor's pickers.
        $options['settings']['datepicker_format'] = 'dd-mm-yy&d-m-Y';
        $options['settings']['time_format'] = '24';

        // MEC's settings screens read these unchecked; until its settings are
        // first saved, each is a warning that Acorn turns into a 500.
        $options['settings'] += [
            'breadcrumbs_events_page' => '',
            'fes_event_link_target' => '',
        ];

        // Colours MEC's generated CSS (.mec-color, buttons, dots) follows; mirrors the theme palette.
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
 * Events without a colour get the theme blue, so every event has a dot on the
 * calendar. Applied when MEC reads the meta (the one key, or all of it as its
 * own loader does), so events saved before this still get it and no data changes.
 */
function mec_default_event_color($value, $postId, $metaKey, $single)
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
add_filter('get_post_metadata', 'mec_default_event_color', 10, 4);
