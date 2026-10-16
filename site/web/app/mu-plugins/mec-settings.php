<?php

/**
 * Plugin Name: Modern Events Calendar settings
 * Description: Settings this site relies on, fixed in code so every environment matches. An mu-plugin because MEC reads and caches its settings while plugins load — before the theme's filters exist.
 */

/*
 * MEC's Cost field is a number input by default ("Numeric" cost type), which
 * can't hold the prices we use — "£5 per child | £7 per solo adult", one
 * bullet per "|" — and loads them as empty, so saving an event wiped its
 * price. Always use MEC's text ("Alphabetic") cost type. "This event is free"
 * still saves 0, which the event template shows as "Free".
 */
add_filter('option_mec_options', function ($options) {
    if (is_array($options)) {
        $options['settings']['single_cost_type'] = 'alphabetic';
    }

    return $options;
});
