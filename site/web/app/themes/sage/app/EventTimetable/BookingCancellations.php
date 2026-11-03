<?php

namespace App\EventTimetable;

/**
 * When a published timetable stops running a session that people have booked (a cancelled
 * session, a closure, a deleted or moved session, shorter dates), cancel those bookings
 * through MEC so each booker gets MEC's cancellation email. The editor shows what will be
 * cancelled and asks first (RestController::preview, App.jsx); this does the work on save.
 *
 * Refunds aren't handled: bookings are cancelled with the refund flag off.
 */
class BookingCancellations
{
    /** booking id => reason, read by the %%cancellation_reason%% placeholder while the email is built. */
    protected static array $reasons = [];

    /** True while a batch is being cancelled: MEC's admin and organiser copies are off, a summary replaces them. */
    protected static bool $batching = false;

    public static function boot(): void
    {
        // MEC has no placeholder for why a booking was cancelled; the cancellation wording can use this one.
        add_filter('mec_render_message_email', function ($message, $bookId) {
            $reason = static::$reasons[(int) $bookId] ?? '';

            return str_replace('%%cancellation_reason%%', $reason !== '' ? sprintf(__('Reason: %s', 'sage'), $reason) : '', (string) $message);
        }, 10, 2);

        // A batch would otherwise email the admin once per booking (and again if the admin is also the
        // booker). Turn those copies off while it runs; the booker still gets theirs.
        add_filter('option_mec_options', function ($options) {
            if (static::$batching && is_array($options)) {
                foreach (['send_to_admin', 'send_to_organizer', 'send_to_additional_organizers'] as $key) {
                    $options['notifications']['cancellation_notification'][$key] = 0;
                }
            }

            return $options;
        }, 99);
    }

    /**
     * Sessions a timetable no longer runs, from MEC custom-days strings, as plain rows
     * with the reason people are told. Sessions already under way or over are left out.
     *
     * @param  array<int, list<string>>  $removedDays  event id => MEC day strings
     * @return list<array{event: int, date: string, start: string, end: string, reason: string}>
     */
    public static function sessions(array $removedDays, array $schedule): array
    {
        $now = strtotime(current_time('Y-m-d H:i:s') . ' UTC');
        $out = [];

        foreach ($removedDays as $event => $days) {
            foreach ($days as $day) {
                $session = Schedule::parseDay($day);
                if ($session && strtotime("{$session['date']} {$session['start']} UTC") > $now) {
                    $out[] = ['event' => (int) $event, 'reason' => Schedule::reasonFor($schedule, (int) $event, $session['date'], $session['start'], $session['end'])] + $session;
                }
            }
        }

        return $out;
    }

    /**
     * Booking ids still live for one session: not cancelled or rejected, and for that
     * date (bookings that cover every date have none of their own to cancel).
     *
     * @return int[]
     */
    public static function bookingsFor(int $event, string $date, string $start): array
    {
        global $wpdb;

        $ids = $wpdb->get_col($wpdb->prepare(
            "SELECT booking_id FROM {$wpdb->prefix}mec_bookings WHERE event_id = %d AND timestamp = %d AND all_occurrences = 0",
            $event,
            strtotime("$date $start UTC"),
        ));

        return array_values(array_filter(array_map('intval', $ids), fn($id) => (int) get_post_meta($id, 'mec_verified', true) !== -1
            && (int) get_post_meta($id, 'mec_confirmed', true) !== -1
            && get_post_status($id) !== 'trash'));
    }

    /**
     * Which of these sessions have bookings, for the editor's warning.
     *
     * @return list<array{event: int, title: string, date: string, start: string, end: string, reason: string, count: int}>
     */
    public static function impact(array $sessions): array
    {
        $out = [];
        foreach ($sessions as $session) {
            if ($count = count(static::bookingsFor($session['event'], $session['date'], $session['start']))) {
                $out[] = $session + ['title' => html_entity_decode(get_the_title($session['event']), ENT_QUOTES), 'count' => $count];
            }
        }

        return $out;
    }

    /**
     * Cancel every live booking on these sessions (refunds off), then send the admin one summary.
     *
     * @return int bookings cancelled
     */
    public static function cancel(array $sessions): int
    {
        $book = \MEC::getInstance('app.libraries.main')->getBook();
        $summary = [];
        $total = 0;

        static::$batching = true;
        try {
            foreach ($sessions as $session) {
                foreach (static::bookingsFor($session['event'], $session['date'], $session['start']) as $bookId) {
                    static::$reasons[$bookId] = $session['reason'];
                    $book->cancel($bookId, false);
                    $summary[$session['event'] . '|' . $session['date'] . '|' . $session['start']]['session'] = $session;
                    $summary[$session['event'] . '|' . $session['date'] . '|' . $session['start']]['bookings'][] = $bookId;
                    $total++;
                }
            }
        } finally {
            static::$batching = false;
        }

        if ($total) {
            static::notifyAdmin($summary, $total);
        }

        return $total;
    }

    /** One email listing what was cancelled and who was told, in place of a copy per booking. */
    protected static function notifyAdmin(array $summary, int $total): void
    {
        $lines = [];
        foreach ($summary as $group) {
            $session = $group['session'];
            $when = gmdate('l jS F Y', strtotime($session['date'] . ' UTC')) . ', ' . \App\Support\EventSessions::timeRange(strtotime("{$session['date']} {$session['start']} UTC"), strtotime("{$session['date']} {$session['end']} UTC"));
            $lines[] = '<strong>' . esc_html(html_entity_decode(get_the_title($session['event']), ENT_QUOTES)) . '</strong>, ' . esc_html($when)
                . ($session['reason'] !== '' ? ' (' . esc_html($session['reason']) . ')' : '');
            foreach ($group['bookings'] as $bookId) {
                $names = array_map(fn($a) => esc_html(trim(($a['name'] ?? '') . ' <' . ($a['email'] ?? '') . '>')), (array) (get_post_meta($bookId, 'mec_attendees', true) ?: []));
                $lines[] = '&nbsp;&nbsp;' . implode('; ', array_slice($names, 0, 1)) . (count($names) > 1 ? sprintf(' +%d', count($names) - 1) : '');
            }
        }

        $body = '<p>' . esc_html(sprintf(_n('The timetable no longer runs a session, so %d booking was cancelled and the booker emailed.', 'The timetable no longer runs sessions, so %d bookings were cancelled and the bookers emailed.', $total, 'sage'), $total)) . '</p>'
            . '<p>' . implode("<br>\n", $lines) . '</p>';

        $notifications = \MEC::getInstance('app.libraries.main')->getNotifications();
        $notifications->mec_sender_email_notification_filter();
        $html = fn() => 'text/html';
        add_filter('wp_mail_content_type', $html);
        wp_mail(
            get_bloginfo('admin_email'),
            sprintf(_n('%d booking cancelled by a timetable change', '%d bookings cancelled by a timetable change', $total, 'sage'), $total),
            apply_filters('mec_email_template', $body),
        );
        remove_filter('wp_mail_content_type', $html);
    }
}
