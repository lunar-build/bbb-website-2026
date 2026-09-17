<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class IconLinkGrid extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Icon Link Grid';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'icon-link-grid';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A row of icon-led links, each with a heading, description, and destination URL.';

    /**
     * The block category.
     *
     * @var string
     */
    public $category = 'text';

    /**
     * The block icon.
     *
     * @var string|array
     */
    public $icon = 'grid-view';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'icon',
        'link',
        'grid',
        'list',
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
            'padding' => false,
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
     * The block template.
     *
     * @var array
     */
    public $template = [
        'core/heading' => ['placeholder' => 'Heading (optional)', 'level' => 2],
    ];

    /**
     * Fixture markup standing in for this block's InnerBlocks content on
     * the pattern library page (see App\View\Composers\PatternLibrary).
     * Empty by default — the Figma reference for this block has no heading
     * above the grid, so the fixture matches that (heading is optional,
     * for callers who do want one).
     *
     * @var string
     */
    public $exampleContent = '';

    /**
     * The block preview example data.
     *
     * @var array
     */
    public $example = [
        'items' => [
            [
                'icon' => 'cycling-route',
                'description' => 'Find a route that suits you. Discover the shortest and safest cycle routes to get you where you want to go',
                'link' => [
                    'title' => 'Plan a cycling route',
                    'url' => 'https://betterbybike.info/routes/',
                    'target' => '',
                ],
            ],
            [
                'icon' => 'bike',
                'description' => 'Borrow one of our FREE electric, hybrid or folding bikes for up to a month',
                'link' => [
                    'title' => 'Need a bike?',
                    'url' => 'https://betterbybike.info/loan-a-bike/',
                    'target' => '',
                ],
            ],
            [
                'icon' => 'cycling-person',
                'description' => 'Training is available for adults, children, and people with learning and physical difficulties',
                'link' => [
                    'title' => 'Learn how to ride',
                    'url' => 'https://betterbybike.info/training/',
                    'target' => '',
                ],
            ],
        ],
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'items' => $this->items(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('icon_link_grid');

        $fields
            ->addRepeater('items', [
                'label' => 'Items',
                'button_label' => 'Add item',
                'min' => 0,
                'layout' => 'block',
            ])
                ->addSelect('icon', [
                    'label' => 'Icon',
                    'choices' => svg_icon_choices(),
                    'required' => 1,
                ])
                ->addLink('link', [
                    'label' => 'Link',
                    'instructions' => 'Link text (used as the item heading) + URL.',
                    'required' => 1,
                ])
                ->addTextarea('description', [
                    'label' => 'Description',
                    'rows' => 2,
                    'required' => 0,
                ])
            ->endRepeater();

        return $fields->build();
    }

    /**
     * Retrieve the items.
     *
     * @return array
     */
    public function items()
    {
        return get_field('items') ?: $this->example['items'];
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
