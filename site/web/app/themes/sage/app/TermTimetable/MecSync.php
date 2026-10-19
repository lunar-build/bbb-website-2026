<?php

namespace App\TermTimetable;

use WP_Post;

/**
 * Writes a published term's sessions into each class's MEC Custom Days.
 *
 * Each term remembers the day strings it generated (TERM_GENERATED), so a
 * re-save only swaps out its own dates: sessions added by hand in MEC, and
 * other terms' sessions, are left alone. Every entry point runs the same
 * idempotent sync(), so being called twice for one save is harmless.
 */
class MecSync
{
    /** On the term: {eventId: [MEC day strings]} it generated last time. */
    public const TERM_GENERATED = '_term_generated_days';

    /** On the term: problems the last sync hit (skipped classes), shown in the editor. */
    public const TERM_NOTICES = '_term_sync_notices';

    /** On the class: when a term last rewrote its dates (ClassEditGuard compares it). */
    public const SYNCED_AT = '_term_synced_at';

    public static function boot(): void
    {
        add_action('rest_after_insert_' . TermTimetable::POST_TYPE, fn(WP_Post $post) => static::sync($post->ID));
        add_action('transition_post_status', function ($new, $old, $post) {
            if ($post->post_type === TermTimetable::POST_TYPE && $new !== $old) {
                static::sync($post->ID);
            }
        }, 10, 3);
        add_action('before_delete_post', function ($postId) {
            if (get_post_type($postId) === TermTimetable::POST_TYPE) {
                static::sync((int) $postId, true);
            }
        });

        if (defined('WP_CLI') && WP_CLI) {
            // `wp term-timetable sync [id]`: re-sync one term, or every term (e.g. after a deploy).
            \WP_CLI::add_command('term-timetable sync', function ($args) {
                $ids = $args ? array_map('intval', $args) : get_posts(['post_type' => TermTimetable::POST_TYPE, 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids']);
                foreach ($ids as $id) {
                    $notices = static::sync($id);
                    \WP_CLI::log("term $id synced" . ($notices ? ': ' . implode('; ', $notices) : ''));
                }
            });
        }
    }

    /**
     * Bring MEC in line with this term: its generated dates if published,
     * none otherwise (draft, trash, or being deleted).
     *
     * @return list<string> notices about classes that were skipped
     */
    public static function sync(int $termId, bool $deleting = false): array
    {
        $previous = (array) get_post_meta($termId, static::TERM_GENERATED, true);
        $published = ! $deleting && get_post_status($termId) === 'publish';
        $wanted = [];
        $notices = [];

        if ($published) {
            $schedule = Schedule::normalise(get_post_meta($termId, TermTimetable::META, true));
            foreach (Schedule::expand($schedule) as $event => $sessions) {
                if (get_post_type($event) !== 'mec-events' || get_post_status($event) === 'trash') {
                    $notices[] = sprintf(__('A class (#%d) no longer exists, so its sessions were skipped.', 'sage'), $event);

                    continue;
                }
                $wanted[$event] = array_map([static::class, 'dayString'], $sessions);
            }
        }

        foreach (array_unique([...array_keys($previous), ...array_keys($wanted)]) as $event) {
            if (get_post_type($event) !== 'mec-events') {
                continue;
            }
            if (static::hasOtherRepeat($event)) {
                $notices[] = sprintf(__('“%s” uses its own repeat rule in MEC, so this term didn\'t change it. Set its repeat to Custom Days (or none) to schedule it here.', 'sage'), get_the_title($event));
                unset($wanted[$event]);

                continue;
            }
            $current = static::currentDays($event);
            $keep = array_diff($current, $previous[$event] ?? []);
            $days = array_values(array_unique([...$keep, ...($wanted[$event] ?? []), ...static::otherTermsDays($termId, $event)]));
            static::writeDays($event, $days);
        }

        if ($wanted) {
            update_post_meta($termId, static::TERM_GENERATED, $wanted);
        } else {
            delete_post_meta($termId, static::TERM_GENERATED);
        }
        update_post_meta($termId, static::TERM_NOTICES, $notices);

        return $notices;
    }

    /** ['date' => '2027-01-02', 'start' => '10:45', 'end' => '11:30'] → "2027-01-02:2027-01-02:10-45-AM:11-30-AM", MEC's custom-days format. */
    public static function dayString(array $session): string
    {
        $time = fn(string $hhmm) => gmdate('h-i-A', strtotime("1970-01-01 $hhmm UTC"));

        return "{$session['date']}:{$session['date']}:{$time($session['start'])}:{$time($session['end'])}";
    }

    /** Daily/weekly/etc. repeats can't be merged with dated sessions, so those classes are left alone. */
    public static function hasOtherRepeat(int $event): bool
    {
        return get_post_meta($event, 'mec_repeat_status', true)
            && ! in_array(get_post_meta($event, 'mec_repeat_type', true), ['', 'custom_days'], true);
    }

    /** The event's sessions as day strings: its start occurrence plus Custom Days (none if it's only a placeholder). */
    protected static function currentDays(int $event): array
    {
        if (get_post_meta($event, '_event_undated', true)) {
            return [];
        }
        $start = (string) get_post_meta($event, 'mec_start_date', true);
        $time = fn(string $which) => sprintf(
            '%02d-%02d-%s',
            (int) get_post_meta($event, "mec_{$which}_time_hour", true),
            (int) get_post_meta($event, "mec_{$which}_time_minutes", true),
            get_post_meta($event, "mec_{$which}_time_ampm", true) ?: 'AM',
        );
        $first = $start ? ["$start:$start:{$time('start')}:{$time('end')}"] : [];
        $custom = array_filter(array_map('trim', explode(',', (string) get_post_meta($event, 'mec_in_days', true))));

        return array_values(array_unique([...$first, ...$custom]));
    }

    /** Day strings other published terms generated for this event, so an overlap isn't removed by mistake. */
    protected static function otherTermsDays(int $termId, int $event): array
    {
        $days = [];
        foreach (get_posts(['post_type' => TermTimetable::POST_TYPE, 'post_status' => 'publish', 'numberposts' => -1, 'exclude' => [$termId], 'fields' => 'ids']) as $other) {
            $days = [...$days, ...((array) get_post_meta($other, static::TERM_GENERATED, true))[$event] ?? []];
        }

        return $days;
    }

    /**
     * Set an event's dates: the earliest becomes MEC's start occurrence, the
     * rest its Custom Days; with none, it keeps its page as an undated event.
     * Writes the same fields MEC's own save does, then rebuilds wp_mec_dates.
     */
    protected static function writeDays(int $event, array $days): void
    {
        global $wpdb;

        usort($days, fn($a, $b) => strcmp(static::sortKey($a), static::sortKey($b)));
        $first = array_shift($days);
        $table = $wpdb->prefix . 'mec_events';

        if ($first === null) {
            update_post_meta($event, '_event_undated', 1);
            update_post_meta($event, 'mec_repeat_status', 0);
            update_post_meta($event, 'mec_repeat_type', '');
            update_post_meta($event, 'mec_in_days', '');
            $wpdb->update($table, ['repeat' => 0, 'days' => ''], ['post_id' => $event]);
        } else {
            [$date, , $start, $end] = explode(':', $first);
            [$sh, $sm, $sa] = explode('-', $start);
            [$eh, $em, $ea] = explode('-', $end);
            $seconds = fn($h, $m, $a) => ((int) $h % 12 + ($a === 'PM' ? 12 : 0)) * 3600 + (int) $m * 60;
            $custom = implode(',', $days);

            delete_post_meta($event, '_event_undated');
            foreach ([
                'mec_start_date' => $date, 'mec_end_date' => $date,
                'mec_start_time_hour' => (int) $sh, 'mec_start_time_minutes' => $sm, 'mec_start_time_ampm' => $sa,
                'mec_end_time_hour' => (int) $eh, 'mec_end_time_minutes' => $em, 'mec_end_time_ampm' => $ea,
                'mec_start_day_seconds' => $seconds($sh, $sm, $sa), 'mec_end_day_seconds' => $seconds($eh, $em, $ea),
                'mec_allday' => 0, 'mec_hide_time' => 0,
                'mec_repeat_status' => $custom ? 1 : 0, 'mec_repeat_type' => $custom ? 'custom_days' : '',
                'mec_in_days' => $custom,
            ] as $key => $value) {
                update_post_meta($event, $key, $value);
            }

            $meta = (array) get_post_meta($event, 'mec_date', true);
            $meta['start'] = ['date' => $date, 'hour' => (int) $sh, 'minutes' => $sm, 'ampm' => $sa];
            $meta['end'] = ['date' => $date, 'hour' => (int) $eh, 'minutes' => $em, 'ampm' => $ea];
            $meta['allday'] = 0;
            $meta['hide_time'] = 0;
            update_post_meta($event, 'mec_date', $meta);

            $row = [
                'start' => $date, 'end' => $date, 'repeat' => $custom ? 1 : 0, 'rinterval' => null,
                'time_start' => $seconds($sh, $sm, $sa), 'time_end' => $seconds($eh, $em, $ea), 'days' => $custom,
            ];
            if ($wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE post_id = %d", $event))) {
                $wpdb->update($table, $row, ['post_id' => $event]);
            } else {
                $wpdb->insert($table, $row + ['post_id' => $event]);
            }
        }

        update_post_meta($event, static::SYNCED_AT, (string) microtime(true));
        static::reschedule($event);
    }

    /** Rebuild an event's wp_mec_dates rows from its current MEC fields. */
    public static function reschedule(int $event): void
    {
        clean_post_cache($event);
        // MEC caches each event's data twice — in the object cache (12h) and its meta
        // per request (MEC_cache) — so its reschedule would otherwise read the old dates.
        wp_cache_delete($event, 'mec-events-data');
        \MEC_cache::delete("meta-$event-0");
        \MEC_cache::delete("meta-$event-1");
        $schedule = \MEC::getInstance('app.libraries.main')->getSchedule();
        $schedule->reschedule($event, $schedule->get_reschedule_maximum((string) get_post_meta($event, 'mec_repeat_type', true), $event));
    }

    /** "2027-01-02:…:01-15-PM:…" → "2027-01-02 13:15", for chronological sorting. */
    protected static function sortKey(string $day): string
    {
        [$date, , $start] = explode(':', $day) + [2 => '12-00-AM'];
        [$h, $m, $a] = explode('-', $start) + [1 => '00', 2 => 'AM'];

        return sprintf('%s %02d:%s', $date, (int) $h % 12 + ($a === 'PM' ? 12 : 0), $m);
    }
}
