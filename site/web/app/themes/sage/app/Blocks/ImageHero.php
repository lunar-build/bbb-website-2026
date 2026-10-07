<?php

namespace App\Blocks;

use Illuminate\Support\Facades\Vite;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class ImageHero extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Image Hero';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A full-bleed image hero, with an optional heading and/or breadcrumbs overlay — covers the "Basic picture" and "Picture with text overlay" Figma variants, heading and breadcrumbs toggled independently.';

    /**
     * The block category.
     *
     * @var string
     */
    public $category = 'media';

    /**
     * The block icon.
     *
     * @var string|array
     */
    public $icon = 'format-image';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'image',
        'hero',
        'picture',
        'breadcrumbs',
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
    public $align = 'full';

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
     * The block preview example data.
     *
     * @var array
     */
    public $example = [
        'show_heading' => true,
        'show_breadcrumbs' => true,
        'heading' => 'Bike shops',
        'image_position' => 'center',
    ];

    /**
     * Computed fixture values (Vite::asset() isn't a constant expr, so
     * can't live in the $example property default).
     */
    public function example(): array
    {
        return [
            'background_image' => ['url' => Vite::asset('resources/images/placeholder/pattern-placeholder.svg')],
        ];
    }

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'backgroundImage' => $this->backgroundImage(),
            'imagePosition' => $this->imagePosition(),
            'showHeading' => $this->showHeading(),
            'showBreadcrumbs' => $this->showBreadcrumbs(),
            'heading' => $this->heading(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('image_hero');

        $fields
            ->addImage('background_image', [
                'label' => 'Background image',
                'required' => true,
                'return_format' => 'array',
                'preview_size' => 'large',
            ])
            ->addSelect('image_position', [
                'label' => 'Image position',
                'instructions' => 'Which part of the image stays visible when it\'s cropped to fit the hero.',
                'choices' => [
                    'top' => 'Top',
                    'center' => 'Center',
                    'bottom' => 'Bottom',
                ],
                'default_value' => 'center',
                'ui' => true,
            ])
            ->addTrueFalse('show_heading', [
                'label' => 'Show heading',
                'instructions' => 'Off (with "Show breadcrumbs" also off) renders a plain full-bleed image with no overlay ("Basic picture").',
                'default_value' => 1,
                'ui' => true,
            ])
            ->addText('heading', [
                'label' => 'Heading',
                'conditional_logic' => [
                    [
                        [
                            'field' => 'show_heading',
                            'operator' => '==',
                            'value' => '1',
                        ],
                    ],
                ],
            ])
            ->addTrueFalse('show_breadcrumbs', [
                'label' => 'Show breadcrumbs',
                'instructions' => 'Independent of "Show heading" — e.g. off here and on in template-sticky-nav.blade.php\'s own hardcoded breadcrumbs instead.',
                'default_value' => 1,
                'ui' => true,
            ]);

        return $fields->build();
    }

    /**
     * Retrieve the background image.
     *
     * @return array|null
     */
    public function backgroundImage()
    {
        return get_field('background_image') ?: ($this->preview ? ($this->example['background_image'] ?? null) : null);
    }

    /**
     * Retrieve the image position (object-position keyword).
     *
     * @return string
     */
    public function imagePosition()
    {
        return get_field('image_position') ?: 'center';
    }

    /**
     * Whether to show the heading within the overlay.
     *
     * @return bool
     */
    public function showHeading()
    {
        return (bool) (get_field('show_heading') ?? ($this->preview ? $this->example['show_heading'] : false));
    }

    /**
     * Whether to show breadcrumbs within the overlay — independent of showHeading().
     *
     * @return bool
     */
    public function showBreadcrumbs()
    {
        return (bool) (get_field('show_breadcrumbs') ?? ($this->preview ? $this->example['show_breadcrumbs'] : false));
    }

    /**
     * Retrieve the heading.
     *
     * @return string
     */
    public function heading()
    {
        return get_field('heading') ?: ($this->preview ? $this->example['heading'] : '');
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
