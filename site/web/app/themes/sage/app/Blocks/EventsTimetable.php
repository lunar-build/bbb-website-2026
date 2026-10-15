<?php

namespace App\Blocks;

use App\Support\EventSessions;
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
     * Pattern-library fixture. Only the layout is a field; the sessions come
     * from MEC, so examplePeriods() supplies them already shaped.
     *
     * @var array
     */
    public $example = [
        'layout' => 'tabs',
    ];

    /**
     * Both layouts on the pattern library (see build-acf-block skill §6).
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
     * - `dates`: every date in the period with its sessions, for the accordion.
     *
     * @return array<int, array{id: string, title: string, weekdays: array, dates: array}>
     */
    public function periods(): array
    {
        $rows = get_field('periods') ?: [];
        if (! $rows) {
            return shows_example($this->preview) ? $this->examplePeriods() : [];
        }
        $category = get_field('category') ?: null;

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
                ];
            }

            foreach (EventSessions::between($row['start_date'], $row['end_date'], $category) as $s) {
                $session = [
                    'time' => EventSessions::timeRange($s->tstart, $s->tend),
                    'title' => $s->title,
                    'url' => get_permalink($s->post_id),
                    'color' => EventSessions::color($s->post_id),
                ];
                $weekdays[gmdate('l', $s->tstart)][$session['time'] . $s->post_id] = $session;
                $dates[gmdate('Y-m-d', $s->tstart)]['sessions'][] = $session;
            }

            return [
                'id' => wp_unique_id('events-timetable-'),
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
