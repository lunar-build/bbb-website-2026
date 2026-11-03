<?php

namespace App\Blocks;

use App\Blocks\Concerns\ResolvesFields;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class EventsCalendar extends Block
{
    use ResolvesFields;

    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Events Calendar';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'events-calendar';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A Modern Events Calendar shortcode (monthly, full calendar, countdown…) in the standard block container and spacing.';

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
        'calendar',
        'mec',
        'countdown',
        'whats on',
    ];

    /**
     * The block post type allow list.
     *
     * @var array
     */
    public $post_types = ['post', 'page'];

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
            'background' => true,
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
     * The block preview example data. -1 stands in for "a calendar is chosen" on the pattern-library
     * page, where there is no real MEC shortcode to pick.
     *
     * @var array
     */
    public $example = [
        'mec_calendar' => -1,
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'calendar' => $this->calendar(),
            'title' => $this->title(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        // The choices are the site's MEC shortcodes. Loaded when the field is shown, not here, as MEC
        // may not have registered its post type yet when blocks are registered.
        add_filter('acf/load_field/name=mec_calendar', function ($field) {
            $field['choices'] = [];

            foreach (get_posts(['post_type' => 'mec_calendars', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC']) as $calendar) {
                $field['choices'][$calendar->ID] = $calendar->post_title;
            }

            return $field;
        });

        $fields = Builder::make('events_calendar');

        $fields
            ->addSelect('mec_calendar', [
                'label' => 'Calendar',
                'instructions' => 'A Modern Events Calendar shortcode. Create and edit them under Events → Shortcodes; the skin (monthly, full calendar, countdown…) and its filters are set there.',
                'choices' => [],
                'ui' => true,
                'allow_null' => true,
            ])
        ;

        return $fields->build();
    }

    /**
     * Retrieve the chosen MEC shortcode's post ID (0 if none).
     *
     * @return int
     */
    public function calendar()
    {
        return (int) $this->resolveField('mec_calendar', 0);
    }

    /**
     * Retrieve the chosen shortcode's name, for the editor and pattern-library placeholder.
     *
     * @return string
     */
    public function title()
    {
        $id = $this->calendar();

        return $id > 0 ? (string) get_the_title($id) : 'Events calendar';
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
