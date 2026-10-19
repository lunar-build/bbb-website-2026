<?php

namespace App\Blocks;

use App\Support\EventSessions;
use App\TermTimetable\Schedule;
use App\TermTimetable\TermTimetable;
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
    public $description = 'A timetable of event sessions, grouped into periods (e.g. term, half term) and shown by weekday tabs or as a day-by-day accordion. Sessions come from the Events calendar.';

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
     * so they stay on those rather than switching to terms.
     */
    protected function source(): string
    {
        return get_field('source') ?: (get_field('periods') ? 'manual' : 'terms');
    }

    /**
     * Published term timetables (App\TermTimetable) that haven't ended, in
     * date order, as period rows — limited to the block's category if set.
     */
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

    protected function termRows(?int $category): array
    {
        $today = current_time('Y-m-d');
        $rows = [];
        foreach (get_posts(['post_type' => TermTimetable::POST_TYPE, 'post_status' => 'publish', 'numberposts' => -1]) as $term) {
            $s = Schedule::normalise(get_post_meta($term->ID, TermTimetable::META, true));
            if (! $s['start'] || $s['end'] < $today || ($category && (int) $s['category'] !== (int) $category)) {
                continue;
            }
            $rows[] = [
                'title' => html_entity_decode(get_the_title($term), ENT_QUOTES) . ': '
                    . gmdate('l jS F', strtotime($s['start'] . ' UTC')) . ' – ' . gmdate('l jS F', strtotime($s['end'] . ' UTC')),
                'start_date' => $s['start'],
                'end_date' => $s['end'],
                'closed' => static::closedDates($s['closures']),
                'weekly' => array_map(fn($x) => $x['weekday'] . '|' . $x['start'] . '|' . $x['end'] . '|' . $x['event'], $s['sessions']),
            ];
        }
        usort($rows, fn($a, $b) => strcmp($a['start_date'], $b['start_date']));

        return $rows;
    }

    /**
     * Pattern-library fixture. Only the layout is a field; the sessions come
     * from MEC, so examplePeriods() supplies them already shaped.
     *
     * @var array
     */
    public $example = [
        'layout' => 'tabs',
    ];

    /**
     * Both layouts on the pattern library.
     *
     * @var array
     */
    public $examples = [
        'Weekday tabs' => ['layout' => 'tabs'],
        'Day-by-day accordion (alt)' => ['layout' => 'accordion'],
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'layout' => get_field('layout') ?: (shows_example($this->preview) ? $this->example['layout'] : 'tabs'),
            'periods' => $this->periods(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('events_timetable');

        $fields
            ->addButtonGroup('layout', [
                'label' => 'Layout',
                'choices' => [
                    'tabs' => 'Weekday tabs',
                    'accordion' => 'Day-by-day accordion',
                ],
                'default_value' => 'tabs',
                'instructions' => 'Weekday tabs show each period\'s weekly pattern. The accordion lists every date in the period.',
            ])
            ->addButtonGroup('source', [
                'label' => 'Periods',
                'choices' => [
                    'terms' => 'Term timetables (automatic)',
                    'manual' => 'Set dates here',
                ],
                'default_value' => 'terms',
                'instructions' => 'Automatic shows every current and upcoming published term from Events → Term timetables, so new terms appear here by themselves.',
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
     * Each period's sessions, grouped two ways:
     * - `weekdays`: Monday–Sunday, each weekly slot once (a term repeats the
     *   same week, so the tabs show the pattern, not every date);
     * - `dates`: every date in the period, for the accordion.
     * `empty` is true when the period has no sessions at all.
     *
     * @return array<int, array{id: string, title: string, empty: bool, weekdays: array, dates: array}>
     */
    public function periods(): array
    {
        $category = get_field('category') ?: null;
        $rows = $this->source() === 'terms' ? $this->termRows($category) : (get_field('periods') ?: []);
        if (! $rows) {
            return shows_example($this->preview) ? $this->examplePeriods() : [];
        }

        return array_map(function (array $row) use ($category) {
            $weekdays = array_fill_keys(self::WEEKDAYS, []);
            $dates = [];

            // List every date in the period, so the accordion shows quiet days too.
            $last = strtotime($row['end_date'] . ' UTC');
            for ($day = strtotime($row['start_date'] . ' UTC'); $day <= $last; $day += DAY_IN_SECONDS) {
                $dates[gmdate('Y-m-d', $day)] = [
                    'label' => gmdate('l jS F', $day),
                    'today' => gmdate('Y-m-d', $day) === current_time('Y-m-d'),
                    'sessions' => [],
                    'closed' => $row['closed'][gmdate('Y-m-d', $day)] ?? null,
                ];
            }

            foreach (EventSessions::between($row['start_date'], $row['end_date'], $category) as $s) {
                $session = [
                    'time' => EventSessions::timeRange($s->tstart, $s->tend),
                    'title' => $s->title,
                    'url' => get_permalink($s->post_id),
                    'color' => EventSessions::color($s->post_id),
                    'labels' => EventSessions::labels($s->post_id),
                ];
                // A term's weekday tabs show its weekly pattern; one-off extras only appear by date.
                if (! isset($row['weekly']) || in_array(gmdate('N', $s->tstart) . '|' . gmdate('H:i', $s->tstart) . '|' . gmdate('H:i', $s->tend) . '|' . $s->post_id, $row['weekly'], true)) {
                    $weekdays[gmdate('l', $s->tstart)][$session['time'] . $s->post_id] = $session;
                }
                $dates[gmdate('Y-m-d', $s->tstart)]['sessions'][] = $session;
            }

            return [
                'id' => wp_unique_id('events-timetable-'),
                'empty' => ! array_filter($weekdays),
                'title' => $row['title'],
                'weekdays' => array_map('array_values', $weekdays),
                'dates' => array_values($dates),
            ];
        }, $rows);
    }

    /**
     * Pattern-library fixture: two holiday weeks, as in the Figma timetables.
     */
    protected function examplePeriods(): array
    {
        $sessions = [
            ['time' => '09:45 — 10:30', 'title' => 'Family Cycling', 'url' => '#', 'color' => '#1EACDB'],
            ['time' => '10:45 — 11:30', 'title' => 'Learn to Ride', 'url' => '#', 'color' => '#F77BA0'],
            ['time' => '11:45 — 12:30', 'title' => 'Family Disability Session', 'url' => '#', 'color' => '#67DD00'],
            ['time' => '13:15 — 14:00', 'title' => 'Learn to Ride', 'url' => '#', 'color' => '#F77BA0'],
            ['time' => '14:15 — 15:00', 'title' => 'Learn to Ride: Next Steps', 'url' => '#', 'color' => '#76DBE8'],
            ['time' => '15:15 — 16:00', 'title' => 'Rock Up and Ride', 'url' => '#', 'color' => '#FFBF47'],
        ];
        $week = function (string $monday) use ($sessions) {
            $dates = [];
            foreach (self::WEEKDAYS as $i => $day) {
                $dates[] = ['label' => gmdate('l jS F', strtotime("$monday +$i days UTC")), 'sessions' => $sessions];
            }

            return [
                'id' => wp_unique_id('events-timetable-example-'),
                'title' => 'Summer Holidays: ' . $dates[0]['label'] . ' – ' . $dates[6]['label'],
                'weekdays' => array_fill_keys(self::WEEKDAYS, $sessions),
                'dates' => $dates,
            ];
        };

        return [$week('2026-08-17'), $week('2026-08-24')];
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
