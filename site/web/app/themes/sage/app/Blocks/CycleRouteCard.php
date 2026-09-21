<?php

namespace App\Blocks;

use Illuminate\Support\Facades\Vite;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class CycleRouteCard extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Cycle Route Card';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'cycle-route-card';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A cycle route card with a difficulty badge, in Easy/Moderate/Difficult styles.';

    /**
     * The block category.
     *
     * @var string
     */
    public $category = 'cards';

    /**
     * The block icon.
     *
     * @var string|array
     */
    public $icon = 'palmtree';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'card',
        'route',
        'cycle',
        'difficulty',
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
        'padding' => null,
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
     * The block preview example data.
     *
     * @var array
     */
    public $example = [
        'difficulty' => 'easy',
        'route_name' => 'Bristol to Bath Railway Path',
        'time_needed' => '2-3 hours',
        'distance' => '13 miles',
        'link' => [
            'title' => 'View route',
            'url' => 'https://betterbybike.info/routes/bristol-to-bath-railway-path/',
            'target' => '',
        ],
    ];

    /**
     * Fallback example data requiring a non-constant expression (Vite::asset).
     *
     * @return array
     */
    public function example(): array
    {
        return [
            'image' => ['url' => Vite::asset('resources/images/placeholder/pattern-placeholder.svg')],
        ];
    }

    /**
     * Difficulty variants to render stacked on the pattern-library page (see
     * App\View\Composers\PatternLibrary::render()) — each entry is merged
     * onto $example above, so only needs to override what differs.
     *
     * @var array
     */
    public $examples = [
        'Easy' => [],
        'Moderate' => [
            'difficulty' => 'moderate',
            'route_name' => 'Brean Down Way',
            'time_needed' => '1 hour',
            'distance' => '5 miles',
        ],
        'Difficult' => [
            'difficulty' => 'difficult',
            'route_name' => 'Mendip Hills Loop',
            'time_needed' => '3-4 hours',
            'distance' => '18 miles',
        ],
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'difficulty' => $this->difficulty(),
            'image' => $this->image(),
            'routeName' => $this->routeName(),
            'timeNeeded' => $this->timeNeeded(),
            'distance' => $this->distance(),
            'link' => $this->link(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('cycle_route_card');

        $fields
            ->addSelect('difficulty', [
                'label' => 'Difficulty',
                'choices' => [
                    'easy' => 'Easy',
                    'moderate' => 'Moderate',
                    'difficult' => 'Difficult',
                ],
                'default_value' => 'easy',
                'ui' => true,
            ])
            ->addImage('image', [
                'label' => 'Image',
                'instructions' => 'Image shown behind the route name banner.',
                'return_format' => 'array',
                'preview_size' => 'medium',
                'required' => 1,
            ])
            ->addText('route_name', [
                'label' => 'Route name',
                'required' => 1,
            ])
            ->addText('time_needed', [
                'label' => 'Time needed',
                'required' => 1,
            ])
            ->addText('distance', [
                'label' => 'Distance',
                'required' => 1,
            ])
            ->addLink('link', [
                'label' => 'Link',
                'instructions' => 'Link text + URL for the CTA.',
                'required' => true,
            ]);

        return $fields->build();
    }

    /**
     * Retrieve the difficulty.
     *
     * @return string
     */
    public function difficulty()
    {
        return get_field('difficulty') ?: $this->example['difficulty'];
    }

    /**
     * Retrieve the image.
     *
     * @return array|null
     */
    public function image()
    {
        return get_field('image') ?: $this->example['image'];
    }

    /**
     * Retrieve the route name.
     *
     * @return string
     */
    public function routeName()
    {
        return get_field('route_name') ?: $this->example['route_name'];
    }

    /**
     * Retrieve the time needed.
     *
     * @return string
     */
    public function timeNeeded()
    {
        return get_field('time_needed') ?: $this->example['time_needed'];
    }

    /**
     * Retrieve the distance.
     *
     * @return string
     */
    public function distance()
    {
        return get_field('distance') ?: $this->example['distance'];
    }

    /**
     * Retrieve the link.
     *
     * @return array
     */
    public function link()
    {
        return get_field('link') ?: $this->example['link'];
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
