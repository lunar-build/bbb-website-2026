<?php

namespace App\Blocks;

use App\Blocks\Concerns\ResolvesFields;
use App\Fields\ContainedToggle;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class JourneyPlannerWidget extends Block
{
    use ResolvesFields;

    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Journey Planner Widget';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A "plan a route" or "find nearby" card that redirects to the external journey planner, meant to sit inside a hero.';

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
    public $icon = 'location-alt';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'journey',
        'route',
        'planner',
        'widget',
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
        'align' => false,
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
     * The block preview example data.
     *
     * @var array
     */
    public $example = [
        'contained' => false,
        'variant' => 'plan_route',
        'heading' => 'Plan a cycling route',
        'cta_label' => 'Plan your route',
        'journey_planner_url' => 'https://cycleplanner.betterbybike.info/route-planning',
    ];

    /**
     * The "find nearby" radio options, fixed because the keys are the cycle planner's `/near-me?type=` values.
     *
     * @var array<string, string> value => label
     */
    public const NEARBY_OPTIONS = [
        'pumps' => 'Public bike pumps',
        'repairs' => 'Bike servicing & repairs',
        'secondhand' => 'Secondhand bike shops',
        'hangars' => 'Cycle hangars',
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'contained' => $this->contained(),
            'variant' => $this->variant(),
            'heading' => $this->heading(),
            'ctaLabel' => $this->ctaLabel(),
            'journeyPlannerUrl' => $this->journeyPlannerUrl(),
            'nearbyOptions' => self::NEARBY_OPTIONS,
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('journey_planner_widget');

        $fields->addPartial(ContainedToggle::class, ['default_value' => 0]);

        $fields
            ->addTab('Content')
                ->addSelect('variant', [
                    'label' => 'Variant',
                    'choices' => [
                        'plan_route' => 'Plan a route',
                        'find_nearby' => 'Find nearby feature',
                    ],
                    'default_value' => 'plan_route',
                    'ui' => true,
                ])
                ->addText('heading', [
                    'label' => 'Heading',
                    'instructions' => 'Leave empty to use the default for the selected variant ("Plan a cycling route" / "What\'s near you…").',
                ])
                ->addText('cta_label', [
                    'label' => 'CTA button label',
                    'instructions' => 'Leave empty to use the default for the selected variant ("Plan your route" / "Search near here").',
                ])
            ->addTab('Journey planner')
                ->addUrl('journey_planner_url', [
                    'label' => 'Journey planner URL',
                    'instructions' => '"Plan a route" redirects to this URL with a `locations` query param appended. "Find nearby" redirects to `/near-me` on the same site.',
                ])
        ;

        return $fields->build();
    }

    /**
     * Determine whether the widget should wrap itself in `.o-container`.
     *
     * @return bool
     */
    public function contained()
    {
        return (bool) (get_field('contained') ?? $this->example['contained'] ?? false);
    }

    /**
     * Retrieve the variant.
     *
     * @return string
     */
    public function variant()
    {
        return $this->resolveField('variant', 'plan_route');
    }

    /**
     * Retrieve the heading, falling back to the variant's default.
     *
     * @return string
     */
    public function heading()
    {
        return get_field('heading') ?: ($this->variant() === 'find_nearby'
            ? "What's near you…"
            : $this->exampleField('heading', ''));
    }

    /**
     * Retrieve the CTA button label, falling back to the variant's default.
     *
     * @return string
     */
    public function ctaLabel()
    {
        return get_field('cta_label') ?: ($this->variant() === 'find_nearby'
            ? 'Search near here'
            : $this->exampleField('cta_label', ''));
    }

    /**
     * Retrieve the journey planner base URL.
     *
     * @return string
     */
    public function journeyPlannerUrl()
    {
        return $this->resolveField('journey_planner_url', '');
    }

    /**
     * Assets enqueued with 'enqueue_block_assets' when rendering the block.
     *
     * Only loads the Google Maps Places script (used by the "Plan a route"
     * variant's From/To autocomplete) when the block is actually present on
     * the page, and only if an API key has been configured.
     *
     * @link https://developer.wordpress.org/block-editor/how-to-guides/enqueueing-assets-in-the-editor/#editor-content-scripts-and-styles
     */
    public function assets(array $block): void
    {
        if (is_admin()) {
            return;
        }

        $apiKey = get_field('google_maps_api_key', 'option');

        if (! $apiKey) {
            return;
        }

        wp_enqueue_script(
            'google-maps-places',
            "https://maps.googleapis.com/maps/api/js?key={$apiKey}&libraries=places&loading=async&callback=initJourneyPlannerPlaces",
            [],
            null,
            true,
        );
    }
}
