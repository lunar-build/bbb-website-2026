<?php

namespace App\EventTimetable;

use DateTimeImmutable;

/**
 * A timetable's schedule: dates, a weekly pattern of sessions, closures,
 * single-date cancellations and one-off extras. Plain PHP, no WordPress, so
 * expand()/validate() are unit-tested directly (tests/Feature/EventScheduleTest.php).
 *
 * Times are wall-clock "HH:MM"; weekdays are ISO 1 (Mon) – 7 (Sun).
 */
class Schedule
{
    public const TIME = '/^([01]\d|2[0-3]):[0-5]\d$/';

    public const DATE = '/^\d{4}-\d{2}-\d{2}$/';

    /** Earliest/latest sensible session times — outside them is a warning, not an error. */
    public const DAY_START = '06:00';

    public const DAY_END = '23:00';

    public static function empty(): array
    {
        return ['start' => '', 'end' => '', 'category' => 0, 'sessions' => [], 'closures' => [], 'cancellations' => [], 'extras' => []];
    }

    /** REST schema for the post meta; WordPress rejects anything that doesn't match. */
    public static function schema(): array
    {
        $time = ['type' => 'string', 'pattern' => '^([01]\d|2[0-3]):[0-5]\d$'];
        $date = ['type' => 'string', 'pattern' => '^\d{4}-\d{2}-\d{2}$'];
        $object = fn(array $properties) => ['type' => 'object', 'properties' => $properties, 'additionalProperties' => false];

        return $object([
            'start' => ['type' => 'string'],
            'end' => ['type' => 'string'],
            'category' => ['type' => 'integer'],
            'sessions' => ['type' => 'array', 'items' => $object([
                'id' => ['type' => 'string'],
                'event' => ['type' => 'integer'],
                'weekday' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 7],
                'start' => $time,
                'end' => $time,
            ])],
            'closures' => ['type' => 'array', 'items' => $object([
                'id' => ['type' => 'string'],
                'from' => $date,
                'to' => $date,
                'label' => ['type' => 'string'],
            ])],
            'cancellations' => ['type' => 'array', 'items' => $object([
                'id' => ['type' => 'string'],
                'date' => $date,
                'session' => ['type' => 'string'],
                'reason' => ['type' => 'string'],
            ])],
            'extras' => ['type' => 'array', 'items' => $object([
                'id' => ['type' => 'string'],
                'date' => $date,
                'event' => ['type' => 'integer'],
                'start' => $time,
                'end' => $time,
            ])],
        ]);
    }

    /** Fills any missing keys, so stored meta from older versions or drafts is safe to read. */
    public static function normalise($schedule): array
    {
        $schedule = is_array($schedule) ? $schedule : [];

        return array_merge(static::empty(), array_intersect_key($schedule, static::empty()));
    }

    /**
     * Problems with a schedule. Errors stop it publishing; warnings are shown
     * but allowed. Each entry: ['message' => string, 'ref' => item id or field].
     *
     * @return array{errors: list<array>, warnings: list<array>}
     */
    public static function validate(array $schedule): array
    {
        $s = static::normalise($schedule);
        $errors = [];
        $warnings = [];

        if (! preg_match(static::DATE, $s['start']) || ! preg_match(static::DATE, $s['end'])) {
            $errors[] = ['message' => 'Set the timetable start and end dates.', 'ref' => 'dates'];
        } elseif ($s['end'] < $s['start']) {
            $errors[] = ['message' => 'The timetable ends before it starts.', 'ref' => 'dates'];
        }

        $timed = fn(array $item, string $what) => array_filter([
            empty($item['event']) ? "Choose an event for this $what." : null,
            ! preg_match(static::TIME, $item['start'] ?? '') || ! preg_match(static::TIME, $item['end'] ?? '') ? "Set a start and end time for this $what." : null,
            preg_match(static::TIME, $item['start'] ?? '') && preg_match(static::TIME, $item['end'] ?? '') && $item['end'] <= $item['start'] ? "This $what ends before it starts." : null,
        ]);

        foreach ($s['sessions'] as $session) {
            foreach ($timed($session, 'session') as $message) {
                $errors[] = ['message' => $message, 'ref' => $session['id'] ?? ''];
            }
            if (($session['start'] ?? '99') < static::DAY_START || ($session['end'] ?? '') > static::DAY_END) {
                $warnings[] = ['message' => 'This session is unusually early or late — check the times.', 'ref' => $session['id'] ?? ''];
            }
        }

        // The same event twice at overlapping times on one day is almost certainly a mistake.
        $byDayEvent = [];
        foreach ($s['sessions'] as $session) {
            $byDayEvent[($session['weekday'] ?? 0) . ':' . ($session['event'] ?? 0)][] = $session;
        }
        foreach ($byDayEvent as $group) {
            usort($group, fn($a, $b) => strcmp($a['start'] ?? '', $b['start'] ?? ''));
            for ($i = 1; $i < count($group); $i++) {
                if (($group[$i]['start'] ?? '') < ($group[$i - 1]['end'] ?? '')) {
                    $errors[] = ['message' => 'This event already has a session at an overlapping time on this day.', 'ref' => $group[$i]['id'] ?? ''];
                }
            }
        }

        foreach ($s['extras'] as $extra) {
            foreach ($timed($extra, 'extra session') as $message) {
                $errors[] = ['message' => $message, 'ref' => $extra['id'] ?? ''];
            }
        }

        $inTimetable = fn(string $date) => $date >= $s['start'] && $date <= $s['end'];
        foreach ($s['closures'] as $closure) {
            if (($closure['to'] ?? '') < ($closure['from'] ?? '')) {
                $errors[] = ['message' => 'This closure ends before it starts.', 'ref' => $closure['id'] ?? ''];
            } elseif (! $inTimetable($closure['from'] ?? '') && ! $inTimetable($closure['to'] ?? '')) {
                $warnings[] = ['message' => 'This closure is outside the timetable, so it has no effect.', 'ref' => $closure['id'] ?? ''];
            }
        }

        $sessionIds = array_column($s['sessions'], 'weekday', 'id');
        foreach ($s['cancellations'] as $cancellation) {
            $weekday = $sessionIds[$cancellation['session'] ?? ''] ?? null;
            if ($weekday === null) {
                $warnings[] = ['message' => 'This cancellation is for a session that no longer exists.', 'ref' => $cancellation['id'] ?? ''];
            } elseif ((int) (new DateTimeImmutable($cancellation['date']))->format('N') !== (int) $weekday) {
                $warnings[] = ['message' => 'This date isn\'t on the session\'s day, so nothing is cancelled.', 'ref' => $cancellation['id'] ?? ''];
            }
        }

        return ['errors' => $errors, 'warnings' => $warnings];
    }

    /**
     * Every dated session the timetable creates, per event: the weekly pattern on
     * each date in the timetable, minus closures and cancellations, plus extras.
     * Sorted by date then start, duplicates removed.
     *
     * @return array<int, list<array{date: string, start: string, end: string}>>
     */
    public static function expand(array $schedule): array
    {
        $s = static::normalise($schedule);
        if (! preg_match(static::DATE, $s['start']) || ! preg_match(static::DATE, $s['end']) || $s['end'] < $s['start']) {
            return [];
        }

        $closed = function (string $date) use ($s) {
            foreach ($s['closures'] as $closure) {
                if ($date >= ($closure['from'] ?? '') && $date <= ($closure['to'] ?? '')) {
                    return true;
                }
            }

            return false;
        };
        $cancelled = [];
        foreach ($s['cancellations'] as $cancellation) {
            $cancelled[($cancellation['session'] ?? '') . '@' . ($cancellation['date'] ?? '')] = true;
        }

        $out = [];
        $add = function (int $event, string $date, string $start, string $end) use (&$out) {
            $out[$event][$date . ' ' . $start . ' ' . $end] = ['date' => $date, 'start' => $start, 'end' => $end];
        };
        $valid = fn(array $item) => ! empty($item['event'])
            && preg_match(static::TIME, $item['start'] ?? '') && preg_match(static::TIME, $item['end'] ?? '')
            && $item['end'] > $item['start'];

        $last = new DateTimeImmutable($s['end']);
        for ($day = new DateTimeImmutable($s['start']); $day <= $last; $day = $day->modify('+1 day')) {
            $date = $day->format('Y-m-d');
            if ($closed($date)) {
                continue;
            }
            foreach ($s['sessions'] as $session) {
                if ((int) ($session['weekday'] ?? 0) === (int) $day->format('N') && $valid($session)
                    && empty($cancelled[($session['id'] ?? '') . '@' . $date])) {
                    $add((int) $session['event'], $date, $session['start'], $session['end']);
                }
            }
        }

        // Extras run even on a closed date: adding one is a deliberate choice.
        foreach ($s['extras'] as $extra) {
            if ($valid($extra) && preg_match(static::DATE, $extra['date'] ?? '')) {
                $add((int) $extra['event'], $extra['date'], $extra['start'], $extra['end']);
            }
        }

        foreach ($out as &$sessions) {
            ksort($sessions);
            $sessions = array_values($sessions);
        }

        return $out;
    }
}
