<?php

namespace App\Blocks;

use App\Support\EventSessions;
use App\EventTimetable\Schedule;
use App\EventTimetable\EventTimetable;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class EventsTimetable extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Events Timetable';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'events-timetable';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A weekly calendar of event sessions, grouped into periods (e.g. term, half term), with each session placed by its start and end time. Sessions come from the Events calendar.';

    /**
     * The block category.
     *
     * @var string
     */
    public $category = 'widgets';

    /**
     * The block icon.
     *
     * @var string|array
     */
    public $icon = 'calendar-alt';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'events',
        'timetable',
        'sessions',
        'whats on',
    ];

    /**
     * The block post type allow list.
     *
     * @var array
     */
    public $post_types = ['page'];

    /**
     * The parent block type allow list.
     *
     * @var array
     */
    public $parent = [];

    /**
     * The ancestor block type allow list.
     *
     * @var array
     */
    public $ancestor = [];

    /**
     * The default block mode.
     *
     * @var string
     */
    public $mode = 'auto';

    /**
     * The default block alignment.
     *
     * @var string
     */
    public $align = '';

    /**
     * The default block text alignment.
     *
     * @var string
     */
    public $align_text = '';

    /**
     * The default block content alignment.
     *
     * @var string
     */
    public $align_content = '';

    /**
     * The default block spacing.
     *
     * @var array
     */
    public $spacing = [
        'padding' => [
            'top' => 'var:preset|spacing|large',
            'bottom' => 'var:preset|spacing|large',
        ],
        'margin' => null,
    ];

    /**
     * The supported block features.
     *
     * @var array
     */
    public $supports = [
        'align' => true,
        'align_text' => false,
        'align_content' => false,
        'full_height' => false,
        'anchor' => false,
        'mode' => true,
        'multiple' => true,
        'jsx' => true,
        'color' => [
            'background' => false,
            'text' => false,
            'gradients' => false,
        ],
        'spacing' => [
            'padding' => ['top', 'bottom'],
            'margin' => false,
        ],
    ];

    /**
     * The block styles.
     *
     * @var array
     */
    public $styles = [];

    /**
     * Weekdays in timetable order (MEC/PHP 'l' names).
     */
    protected const WEEKDAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    /**
     * Blocks saved before the "source" field existed have periods typed in,
     * so they stay on those rather than switching to timetables.
     */
    protected function source(): string
    {
        $source = get_field('source') ?: (get_field('periods') ? 'manual' : 'timetables');

        // 'terms' was this value's name before term timetables became event timetables.
        return $source === 'terms' ? 'timetables' : $source;
    }

    /**
     * Closure date ranges as ['Y-m-d' => reason]; "Closed" when no reason was given.
     */
    protected static function closedDates(array $closures): array
    {
        $days = [];
        foreach ($closures as $closure) {
            for ($day = strtotime($closure['from'] . ' UTC'); $day <= strtotime($closure['to'] . ' UTC'); $day += DAY_IN_SECONDS) {
                $days[gmdate('Y-m-d', $day)] = trim($closure['label'] ?? '') ?: __('Closed', 'sage');
            }
        }

        return $days;
    }

    /**
     * Cancelled sessions as ['date', 'start', 'end', 'event', 'reason'], for
     * the notices. MEC has no cancelled state per date, so these come from
     * the timetable rather than MEC. Dates a closure covers are left out: the
     * closure notice already says why nothing runs.
     */
    protected static function cancelledSessions(array $s): array
    {
        $closed = static::closedDates($s['closures']);
        $sessions = array_column($s['sessions'], null, 'id');
        $out = [];
        foreach ($s['cancellations'] as $cancellation) {
            $session = $sessions[$cancellation['session'] ?? ''] ?? null;
            $date = $cancellation['date'] ?? '';
            if (! $session || isset($closed[$date]) || (int) gmdate('N', strtotime($date . ' UTC')) !== (int) $session['weekday']) {
                continue;
            }
            $out[] = [
                'date' => $date,
                'start' => $session['start'],
                'end' => $session['end'],
                'event' => (int) $session['event'],
                'reason' => trim($cancellation['reason'] ?? ''),
            ];
        }

        return $out;
    }

    /**
     * Upcoming closures, cancellations and extra sessions as short lines. The
     * calendar shows the weekly pattern, not dates, so these carry the changes.
     *
     * @return list<array{type: string, when: string, text: string}>
     */
    protected static function notices(array $row): array
    {
        $today = current_time('Y-m-d');
        $day = fn(string $ymd) => gmdate('D j M', strtotime($ymd . ' UTC'));
        $out = [];

        foreach ($row['closures'] ?? [] as $c) {
            if (($c['to'] ?? '') < $today) {
                continue;
            }
            $out[] = [
                'sort' => $c['from'] . ' 00:00',
                'type' => 'closed',
                'when' => $c['from'] === $c['to'] ? $day($c['from']) : $day($c['from']) . ' – ' . $day($c['to']),
                'text' => trim($c['label'] ?? '') ?: __('Closed', 'sage'),
            ];
        }
        foreach ($row['cancelled'] ?? [] as $c) {
            if ($c['date'] < $today) {
                continue;
            }
            $out[] = [
                'sort' => $c['date'] . ' ' . $c['start'],
                'type' => 'cancelled',
                'when' => $day($c['date']) . ', ' . EventSessions::timeRange(strtotime("{$c['date']} {$c['start']} UTC"), strtotime("{$c['date']} {$c['end']} UTC")),
                'text' => sprintf(__('%s cancelled', 'sage'), html_entity_decode(get_the_title($c['event']), ENT_QUOTES)) . ($c['reason'] ? ': ' . $c['reason'] : ''),
            ];
        }
        foreach ($row['extras'] ?? [] as $x) {
            if (($x['date'] ?? '') < $today || get_post_status((int) $x['event']) !== 'publish') {
                continue;
            }
            $out[] = [
                'sort' => $x['date'] . ' ' . $x['start'],
                'type' => 'extra',
                'when' => $day($x['date']) . ', ' . EventSessions::timeRange(strtotime("{$x['date']} {$x['start']} UTC"), strtotime("{$x['date']} {$x['end']} UTC")),
                'text' => sprintf(__('Extra session: %s', 'sage'), html_entity_decode(get_the_title((int) $x['event']), ENT_QUOTES)),
            ];
        }
        usort($out, fn($a, $b) => strcmp($a['sort'], $b['sort']));

        return array_map(fn($n) => array_diff_key($n, ['sort' => 1]), $out);
    }

    protected function timetableRows(?int $category): array
    {
        $today = current_time('Y-m-d');
        $rows = [];
        foreach (get_posts(['post_type' => EventTimetable::POST_TYPE, 'post_status' => 'publish', 'numberposts' => -1]) as $timetable) {
            $s = Schedule::normalise(get_post_meta($timetable->ID, EventTimetable::META, true));
            if (! $s['start'] || $s['end'] < $today || ($category && (int) $s['category'] !== (int) $category)) {
                continue;
            }
            $rows[] = [
                'title' => html_entity_decode(get_the_title($timetable), ENT_QUOTES) . ': '
                    . gmdate('l jS F', strtotime($s['start'] . ' UTC')) . ' – ' . gmdate('l jS F', strtotime($s['end'] . ' UTC')),
                'start_date' => $s['start'],
                'end_date' => $s['end'],
                'closed' => static::closedDates($s['closures']),
                'cancelled' => static::cancelledSessions($s),
                'closures' => $s['closures'],
                'extras' => $s['extras'],
                'weekly' => array_map(fn($x) => $x['weekday'] . '|' . $x['start'] . '|' . $x['end'] . '|' . $x['event'], $s['sessions']),
            ];
        }
        usort($rows, fn($a, $b) => strcmp($a['start_date'], $b['start_date']));

        return $rows;
    }

    /**
     * Data to be passed to the block before rendering. The sessions come from
     * MEC rather than fields, so the pattern-library fixture is examplePeriods().
     */
    public function with(): array
    {
        $headingLevel = (int) (get_field('heading_level') ?: 2);

        return [
            'periods' => $this->periods(),
            'headingLevel' => $headingLevel,
            // Each day's name sits one level below its period title.
            'dayHeadingLevel' => min($headingLevel + 1, 6),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('events_timetable');

        $fields
            ->addButtonGroup('source', [
                'label' => 'Periods',
                'choices' => [
                    'timetables' => 'Event timetables (automatic)',
                    'manual' => 'Set dates here',
                ],
                'default_value' => 'timetables',
                'instructions' => 'Automatic shows every current and upcoming published timetable from Events → Event timetables, so new timetables appear here by themselves.',
            ])
            ->addSelect('heading_level', [
                'label' => 'Period heading level',
                'choices' => [
                    2 => 'H2',
                    3 => 'H3',
                    4 => 'H4',
                    5 => 'H5',
                ],
                'default_value' => 2,
                'instructions' => 'Heading level for each period title. Choose the level one below the nearest heading above this block, so the page outline stays in order (e.g. H2 if the block sits straight under the page title). The day names inside the timetable use the next level down.',
            ])
            ->addTaxonomy('category', [
                'label' => 'Event category',
                'instructions' => 'Only show sessions from this category. Leave empty for all events.',
                'taxonomy' => 'mec_category',
                'field_type' => 'select',
                'allow_null' => 1,
                'add_term' => 0,
                'save_terms' => 0,
                'load_terms' => 0,
                'return_format' => 'id',
            ])
            ->addRepeater('periods', [
                'label' => 'Periods',
                'conditional_logic' => [[['field' => 'source', 'operator' => '==', 'value' => 'manual']]],
                'instructions' => 'One panel per period, e.g. "Half Term: Monday 26th October – Sunday 1st November". Sessions are pulled from the Events calendar between the two dates.',
                'button_label' => 'Add period',
                'min' => 1,
                'layout' => 'block',
            ])
                ->addText('title', [
                    'label' => 'Title',
                    'required' => 1,
                ])
                ->addDatePicker('start_date', [
                    'label' => 'Start date',
                    'required' => 1,
                    'display_format' => 'd/m/Y',
                    'return_format' => 'Y-m-d',
                    'wrapper' => ['width' => 50],
                ])
                ->addDatePicker('end_date', [
                    'label' => 'End date',
                    'required' => 1,
                    'display_format' => 'd/m/Y',
                    'return_format' => 'Y-m-d',
                    'wrapper' => ['width' => 50],
                ])
            ->endRepeater();

        return $fields->build();
    }

    /**
     * Minutes since midnight for a MEC timestamp (wall-clock stored as UTC).
     */
    protected static function minutes(int $timestamp): int
    {
        return (int) gmdate('G', $timestamp) * 60 + (int) gmdate('i', $timestamp);
    }

    /**
     * Sets `lane` and `lanes` on a day's sessions so ones that overlap in time
     * sit side by side instead of on top of each other. Sessions in the same
     * run of overlaps share a `lanes` count; a lone session gets 1 of 1.
     *
     * @param  array<int, array{start: int, end: int}>  $sessions
     */
    protected static function placeLanes(array $sessions): array
    {
        usort($sessions, fn($a, $b) => [$a['start'], $a['end']] <=> [$b['start'], $b['end']]);

        $run = [];
        $runEnd = 0;
        $laneEnds = [];
        $flush = function () use (&$run, &$laneEnds, &$sessions) {
            foreach ($run as $i) {
                $sessions[$i]['lanes'] = count($laneEnds);
            }
            $run = [];
            $laneEnds = [];
        };

        foreach ($sessions as $i => $session) {
            if ($run && $session['start'] >= $runEnd) {
                $flush();
                $runEnd = 0;
            }
            $lane = array_search(true, array_map(fn($end) => $end <= $session['start'], $laneEnds), true);
            $lane = $lane === false ? count($laneEnds) : $lane;
            $laneEnds[$lane] = $session['end'];
            $sessions[$i]['lane'] = $lane;
            $run[] = $i;
            $runEnd = max($runEnd, $session['end']);
        }
        $flush();

        return $sessions;
    }

    /**
     * The calendar's time axis for a period: whole hours from just before the
     * earliest session to just after the latest, defaulting to 10:00–16:00
     * when there are none.
     *
     * @param  array<string, array<int, array{start: int, end: int}>>  $weekdays
     * @return array{from: int, to: int, hours: array<int, array{minutes: int, label: string}>}
     */
    protected static function grid(array $weekdays): array
    {
        $sessions = array_merge(...array_values($weekdays));
        $from = $sessions ? (int) floor(min(array_column($sessions, 'start')) / 60) * 60 : 600;
        $to = $sessions ? (int) ceil(max(array_column($sessions, 'end')) / 60) * 60 : 960;

        $hours = [];
        for ($m = $from; $m <= $to; $m += 60) {
            $hours[] = ['minutes' => $m, 'label' => sprintf('%02d:00', intdiv($m, 60))];
        }

        return ['from' => $from, 'to' => $to, 'hours' => $hours];
    }

    /**
     * Each period's weekly calendar data:
     * - `weekdays`: Monday–Sunday, each weekly slot once (a timetable repeats the
     *   same week, so the calendar shows the pattern, not every date). Sessions
     *   carry `start`/`end` (minutes since midnight) and `lane`/`lanes` for
     *   overlaps;
     * - `grid`: the hour axis the sessions are positioned against;
     * - `active`: the day shown first on small screens.
     * `empty` is true when the period has no sessions at all.
     *
     * @return array<int, array{id: string, title: string, empty: bool, notices: array, weekdays: array, grid: array, active: string}>
     */
    public function periods(): array
    {
        $category = get_field('category') ?: null;
        $rows = $this->source() === 'timetables' ? $this->timetableRows($category) : (get_field('periods') ?: []);
        if (! $rows) {
            return shows_example($this->preview) ? $this->examplePeriods() : [];
        }

        return array_map(function (array $row) use ($category) {
            $weekdays = array_fill_keys(self::WEEKDAYS, []);

            foreach (EventSessions::between($row['start_date'], $row['end_date'], $category) as $s) {
                // A timetable's calendar shows its weekly pattern; one-off extras are listed in the notices.
                if (isset($row['weekly']) && ! in_array(gmdate('N', $s->tstart) . '|' . gmdate('H:i', $s->tstart) . '|' . gmdate('H:i', $s->tend) . '|' . $s->post_id, $row['weekly'], true)) {
                    continue;
                }
                $time = EventSessions::timeRange($s->tstart, $s->tend);
                $start = static::minutes($s->tstart);
                $weekdays[gmdate('l', $s->tstart)][$time . $s->post_id] = [
                    'time' => $time,
                    'start' => $start,
                    // A session running past midnight stops at the end of its day.
                    'end' => max($start + 1, $s->tend > $s->tstart && gmdate('Y-m-d', $s->tend) === gmdate('Y-m-d', $s->tstart) ? static::minutes($s->tend) : 1440),
                    'title' => $s->title,
                    'url' => get_permalink($s->post_id),
                    'color' => EventSessions::color($s->post_id),
                    'labels' => EventSessions::labels($s->post_id),
                ];
            }

            $weekdays = array_map(fn($sessions) => static::placeLanes(array_values($sessions)), $weekdays);

            return [
                'id' => wp_unique_id('events-timetable-'),
                'empty' => ! array_filter($weekdays),
                'title' => $row['title'],
                'notices' => static::notices($row),
                'weekdays' => $weekdays,
                'grid' => static::grid($weekdays),
                'active' => array_key_first(array_filter($weekdays)) ?? 'Monday',
            ];
        }, $rows);
    }

    /**
     * Pattern-library fixture: two holiday weeks, as in the Figma timetable.
     */
    protected function examplePeriods(): array
    {
        $pink = '#F47096';
        $blue = '#23A3D2';
        $cyan = '#6DD6E3';
        $yellow = '#FFBF47';

        $session = function (string $title, string $from, string $to, string $color) {
            [$h1, $m1] = array_map('intval', explode(':', $from));
            [$h2, $m2] = array_map('intval', explode(':', $to));

            return [
                'time' => $from . ' — ' . $to,
                'start' => $h1 * 60 + $m1,
                'end' => $h2 * 60 + $m2,
                'title' => $title,
                'url' => '#',
                'color' => $color,
                'labels' => [],
            ];
        };

        $schoolDay = [
            $session('Schools', '10:00', '10:45', $pink),
            $session('Schools', '10:45', '11:30', $pink),
            $session('Schools', '11:45', '12:30', $pink),
            $session('Schools', '13:15', '14:00', $pink),
            $session('Learn to Ride', '14:15', '15:00', $yellow),
            $session('Rock Up and Ride', '15:15', '16:00', $cyan),
        ];
        $weekendDay = [
            $session('Family Cycling', '10:00', '11:00', $blue),
            $session('Learn to Ride', '11:15', '12:15', $yellow),
            $session('Family Disability Session', '13:00', '14:00', $pink),
            $session('Learn to Ride', '14:30', '15:30', $yellow),
            $session('Learn to Ride: Next Steps', '16:00', '17:00', $yellow),
            $session('Rock Up and Ride', '17:15', '18:00', $cyan),
        ];

        $week = function (string $monday) use ($schoolDay, $weekendDay) {
            $weekdays = array_fill_keys(self::WEEKDAYS, $schoolDay);
            $weekdays['Saturday'] = $weekdays['Sunday'] = $weekendDay;
            $weekdays = array_map(fn($sessions) => static::placeLanes($sessions), $weekdays);

            return [
                'id' => wp_unique_id('events-timetable-example-'),
                'empty' => false,
                'title' => 'Summer Holidays: ' . gmdate('l jS F', strtotime("$monday UTC")) . ' – ' . gmdate('l jS F', strtotime("$monday +6 days UTC")),
                'notices' => [],
                'weekdays' => $weekdays,
                'grid' => static::grid($weekdays),
                'active' => 'Monday',
            ];
        };

        return [$week('2026-08-24'), $week('2026-08-31')];
    }

    /**
     * Assets enqueued with 'enqueue_block_assets' when rendering the block.
     *
     * @link https://developer.wordpress.org/block-editor/how-to-guides/enqueueing-assets-in-the-editor/#editor-content-scripts-and-styles
     */
    public function assets(array $block): void
    {
        //
    }
}
