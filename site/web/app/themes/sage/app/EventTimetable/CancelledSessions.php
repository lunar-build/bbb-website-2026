<?php

namespace App\EventTimetable;

/**
 * An event's upcoming sessions that its published timetables have called off, one date at a
 * time (a cancelled session, or a closure over a day it would have run). The event page lists
 * them so someone who knew about the session sees why it has gone. MEC no longer holds those
 * dates, so they only exist in the timetables.
 */
class CancelledSessions
{
    /** How far ahead a closure is looked at, so an open-ended one stays cheap. */
    protected const DAYS_AHEAD = 120;

    /**
     * @return list<array{date: string, start: string, end: string, reason: string}> soonest first
     */
    public static function forEvent(int $event, int $limit = 6): array
    {
        $now = strtotime(current_time('Y-m-d H:i:s') . ' UTC');
        $rows = [];
        $add = function (string $date, array $session, string $reason) use (&$rows, $now) {
            if (strtotime("$date {$session['start']} UTC") > $now) {
                $rows["$date {$session['start']}"] = ['date' => $date, 'start' => $session['start'], 'end' => $session['end'], 'reason' => trim($reason)];
            }
        };

        foreach (get_posts(['post_type' => EventTimetable::POST_TYPE, 'post_status' => 'publish', 'numberposts' => -1]) as $timetable) {
            $schedule = Schedule::normalise(get_post_meta($timetable->ID, EventTimetable::META, true));
            $sessions = array_values(array_filter($schedule['sessions'], fn($session) => (int) ($session['event'] ?? 0) === $event));
            if (! $sessions || ! $schedule['start'] || ! $schedule['end']) {
                continue;
            }

            foreach ($schedule['cancellations'] as $cancellation) {
                $date = $cancellation['date'] ?? '';
                foreach ($sessions as $session) {
                    if (($session['id'] ?? '') === ($cancellation['session'] ?? null) && $date >= $schedule['start'] && $date <= $schedule['end']) {
                        $add($date, $session, $cancellation['reason'] ?? '');
                    }
                }
            }

            $horizon = gmdate('Y-m-d', $now + self::DAYS_AHEAD * DAY_IN_SECONDS);
            foreach ($schedule['closures'] as $closure) {
                $from = max($closure['from'] ?? '', $schedule['start'], gmdate('Y-m-d', $now));
                $to = min($closure['to'] ?? '', $schedule['end'], $horizon);
                for ($day = strtotime("$from UTC"); $from && $day <= strtotime("$to UTC"); $day += DAY_IN_SECONDS) {
                    foreach ($sessions as $session) {
                        if ((int) ($session['weekday'] ?? 0) === (int) gmdate('N', $day)) {
                            $add(gmdate('Y-m-d', $day), $session, $closure['label'] ?? '');
                        }
                    }
                }
            }
        }

        ksort($rows);

        return array_slice(array_values($rows), 0, $limit);
    }
}
