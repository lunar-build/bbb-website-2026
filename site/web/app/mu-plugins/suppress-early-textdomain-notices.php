<?php
/**
 * Plugin Name: Suppress early-textdomain _doing_it_wrong notices
 * Description: Silences WP 6.7+'s "Translation loading … triggered too early" notice for specific plugin text domains known to load translations on `plugins_loaded` (before `after_setup_theme`), which is otherwise harmless noise on every page load in debug mode.
 */

add_filter('doing_it_wrong_trigger_error', function ($trigger, $function_name, $message) {
    if ($function_name !== '_load_textdomain_just_in_time') {
        return $trigger;
    }

    $earlyLoadingDomains = [
        // Modern Events Calendar Pro hooks load_languages() on plugins_loaded.
        'mec',
        'modern-events-calendar-lite',
    ];

    foreach ($earlyLoadingDomains as $domain) {
        if (str_contains($message, "<code>{$domain}</code>")) {
            return false;
        }
    }

    return $trigger;
}, 10, 3);
