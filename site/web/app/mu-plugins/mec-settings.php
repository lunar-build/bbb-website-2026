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

        // Cancelled and rejected bookings (including every booking on a cancelled session)
        // must tell the booker. Both emails ship disabled, and cancellation is admin-only.
        foreach (['cancellation_notification', 'booking_rejection'] as $notification) {
            $options['notifications'][$notification]['status'] = 1;
            $options['notifications'][$notification]['send_to_user'] = 1;
            $options['notifications'][$notification]['send_to_admin'] = 1;
        }

        // MEC's settings screens read these unchecked; until its settings are
        // first saved, each is a warning that Acorn turns into a 500.
        $options['settings'] += [
            'breadcrumbs_events_page' => '',
            'fes_event_link_target' => '',
        ];
    }

    return $options;
});

/*
 * MEC's emails print dates and times in WordPress's general date and time formats, which
 * default to "7 November 2026" and 24-hour "13:00". The event pages read "Saturday 7th
 * November 2026" and "1.00pm", so the emails match. (Shown, locked, on Settings → General.)
 */
add_filter('option_date_format', fn() => 'l jS F Y');
add_filter('option_time_format', fn() => 'g.ia');

/*
 * Starting wording for the booking emails. MEC's defaults never name the event, give no time, and
 * leave "calling ." when an event has no organiser phone. Written to the database once per
 * environment (below), so after that the client edits it under Events → Settings → Notifications.
 * Bump BBB_MEC_EMAILS_VERSION to write it again, which overwrites their edits to these six.
 *
 * The theme's email wrapper (`mec_email_template` in filters.php) styles `bbb-button` links, so
 * give a link that class to make it a button. MEC's calendar placeholders are ready-made links.
 */
const BBB_MEC_EMAILS_VERSION = 1;

function bbb_mec_email_wording(): array
{
    $emails = [
        'email_verification' => ['Please verify your booking for %%event_title%%', 'verification'],
        'booking_notification' => ['We have received your booking for %%event_title%%', 'received'],
        'booking_confirmation' => ['Your booking for %%event_title%% is confirmed', 'confirmed'],
        'cancellation_notification' => ['Your booking for %%event_title%% was cancelled', 'cancelled'],
        'booking_rejection' => ["We couldn't take your booking for %%event_title%%", 'rejected'],
        'admin_notification' => ['New booking: %%event_title%%', 'admin'],
    ];

    return array_map(
        fn(array $email) => [$email[0], trim(view("emails.notifications.{$email[1]}")->render())],
        $emails,
    );
}

add_action('init', function () {
    if ((int) get_option('bbb_mec_emails_version') >= BBB_MEC_EMAILS_VERSION) {
        return;
    }

    $options = get_option('mec_options');
    if (! is_array($options)) {
        return;
    }

    foreach (bbb_mec_email_wording() as $key => [$subject, $body]) {
        $options['notifications'][$key]['subject'] = $subject;
        $options['notifications'][$key]['content'] = $body . "\n\nRegards,\n%%blog_name%%";
    }

    update_option('mec_options', $options);
    update_option('bbb_mec_emails_version', BBB_MEC_EMAILS_VERSION);
}, 20);
