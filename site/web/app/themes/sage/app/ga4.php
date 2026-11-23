<?php

/**
 * GA4 event tracking: Google Tag Manager output and the data analytics.js needs.
 */

namespace App;

/**
 * Google Tag Manager. Output only when GTM_CONTAINER_ID is set, so local and production stay untagged
 * until the container (with Cookiebot consent) is ready. Events come from resources/js/analytics.js.
 */
add_action('wp_head', function () {
    $containerId = env('GTM_CONTAINER_ID');

    if (! $containerId) {
        return;
    }

    printf(
        "<script>window.dataLayer=window.dataLayer||[];(function(w,d,s,l,i){w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer',%s);</script>\n",
        wp_json_encode($containerId),
    );
}, 1);

add_action('wp_body_open', function () {
    $containerId = env('GTM_CONTAINER_ID');

    if (! $containerId) {
        return;
    }

    printf(
        '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=%s" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>' . "\n",
        esc_attr($containerId),
    );
});

/**
 * Gravity Forms titles for the form_submission event (analytics.js only gets the form ID). Collected from
 * the forms rendered on this page, so there's no extra query, and printed in the footer once they're known.
 */
add_filter('gform_pre_render', function ($form) {
    $GLOBALS['analytics_form_names'][$form['id']] = $form['title'];

    return $form;
});

add_action('wp_footer', function () {
    if (! env('GTM_CONTAINER_ID') || empty($GLOBALS['analytics_form_names'])) {
        return;
    }

    echo '<script>window.analyticsFormNames=' . wp_json_encode($GLOBALS['analytics_form_names']) . ";</script>\n";
}, 1);
