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

        // Free bookings confirm themselves, but MEC skips the "booking confirmed" email
        // for auto-confirmed ones unless this is on, leaving customers with only "received".
        $options['settings']['booking_auto_confirm_send_email'] = 1;

        // MEC's grey default wrapper off, so the theme's branded one (the `mec_email_template`
        // filter in the theme's filters.php) is used instead.
        $options['settings']['notif_template_disable'] = 1;

        // Verify link as a button (the theme's email wrapper styles `bbb-button` links).
        $options['notifications']['email_verification']['content'] = "Hi %%name%%,\r\n\r\n"
            . "Please verify your booking by clicking the button below.\r\n\r\n"
            . "<a class=\"bbb-button\" href=\"%%verification_link%%\">Verify your booking</a>\r\n\r\n"
            . "Regards,\r\n%%blog_name%%";

        // The default says "we will check and confirm your booking", which is wrong for an
        // auto-confirmed one. This reads correctly either way: the confirmed email follows.
        $options['notifications']['booking_notification']['content'] = "Hello %%name%%,\r\n\r\n"
            . "Thanks for your booking. You'll get another email as soon as it's confirmed.\r\n\r\n"
            . "Regards,\r\n%%blog_name%%";

        // MEC's settings screens read these unchecked; until its settings are
        // first saved, each is a warning that Acorn turns into a 500.
        $options['settings'] += [
            'breadcrumbs_events_page' => '',
            'fes_event_link_target' => '',
        ];
    }

    return $options;
});
