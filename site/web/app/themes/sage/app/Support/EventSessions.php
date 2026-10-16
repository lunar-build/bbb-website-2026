<?php

namespace App\Support;

/**
 * Reads event sessions straight from MEC's own occurrence table
 * (`{prefix}mec_dates`, one row per dated session), which MEC rebuilds
 * whenever an event is saved. We use MEC as the data layer only; every
 * template that lists sessions goes through here instead of MEC's skins.
 *
 * MEC stores each session's wall-clock time as if it were UTC (13:15 is
 * saved as 13:15 UTC whatever the site timezone), so times are formatted
 * with gmdate(), never wp_date().
 */
class EventSessions
{
    /**
     * Published sessions starting between two dates (inclusive, Y-m-d),
     * optionally limited to one mec_category term, ordered by start time.
     *
     * @return array<int, object{post_id: int, title: string, tstart: int, tend: int}>
     */
    public static function between(string $from, string $to, ?int $categoryId = null): array
    {
        global $wpdb;

        $category = $categoryId ? $wpdb->prepare(
            "AND p.ID IN (SELECT tr.object_id FROM {$wpdb->term_relationships} tr
                JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                WHERE tt.taxonomy = 'mec_category' AND tt.term_id = %d)",
            $categoryId,
        ) : '';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT d.post_id, p.post_title AS title, d.tstart, d.tend
            FROM {$wpdb->prefix}mec_dates d
            JOIN {$wpdb->posts} p ON p.ID = d.post_id
            WHERE p.post_type = 'mec-events' AND p.post_status = 'publish'
                AND d.dstart BETWEEN %s AND %s {$category}
            ORDER BY d.tstart, p.post_title",
            $from,
            $to,
        ));
    }

    /**
     * An event's next session from now, or null when none is scheduled.
     *
     * @return object{tstart: int, tend: int}|null
     */
    public static function next(int $postId): ?object
    {
        global $wpdb;

        // Compare against the current wall-clock time stored the same way MEC does.
        $now = strtotime(current_time('Y-m-d H:i:s') . ' UTC');

        return $wpdb->get_row($wpdb->prepare(
            "SELECT tstart, tend FROM {$wpdb->prefix}mec_dates
            WHERE post_id = %d AND tstart >= %d ORDER BY tstart LIMIT 1",
            $postId,
            $now,
        ));
    }

    /**
     * An event's most recent session before now, or null.
     *
     * @return object{tstart: int, tend: int}|null
     */
    public static function last(int $postId): ?object
    {
        global $wpdb;

        $now = strtotime(current_time('Y-m-d H:i:s') . ' UTC');

        return $wpdb->get_row($wpdb->prepare(
            "SELECT tstart, tend FROM {$wpdb->prefix}mec_dates
            WHERE post_id = %d AND tstart < %d ORDER BY tstart DESC LIMIT 1",
            $postId,
            $now,
        ));
    }

    /**
     * The session to show for an event: the next one, or for an event that
     * has finished, its last one, so a past one-off event still shows when
     * it ran. Events flagged `_event_undated` (sessions with no timetable
     * slots, imported on a placeholder date) never show a past date.
     *
     * @return array{session: object{tstart: int, tend: int}, upcoming: bool}|null
     */
    public static function forDisplay(int $postId): ?array
    {
        if ($next = static::next($postId)) {
            return ['session' => $next, 'upcoming' => true];
        }
        if (get_post_meta($postId, '_event_undated', true) || ! ($last = static::last($postId))) {
            return null;
        }

        return ['session' => $last, 'upcoming' => false];
    }

    /**
     * "09:45 — 10:30", as the timetable designs show session times.
     */
    public static function timeRange(int $start, int $end): string
    {
        return gmdate('H:i', $start) . ' — ' . gmdate('H:i', $end);
    }

    /**
     * The colour editors pick per event in MEC (stored without the #).
     */
    public static function color(int $postId): string
    {
        $color = ltrim((string) get_post_meta($postId, 'mec_color', true), '#');

        return $color ? '#' . $color : 'var(--wp--preset--color--blue)';
    }
}
