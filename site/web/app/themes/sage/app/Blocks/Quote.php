<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class Quote extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Quote';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'quote';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A pull quote with optional attribution name and role.';

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
    public $icon = 'format-quote';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'quote',
        'pull quote',
        'testimonial',
        'attribution',
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
     * The block preview example data.
     *
     * Figma showed separate "Short" (quote only) and "Long" (quote +
     * attribution) variants — merged here into a single block where
     * attribution is simply optional, so the fixture below deliberately
     * includes both name + role to show the richest case on the pattern
     * library page (see build-acf-block skill §6).
     *
     * @var array
     */
    public $example = [
        'attribution_name' => 'Priya Chandra',
        'attribution_role' => 'Cabinet Member for Sustainable Transport Delivery',
    ];

    /**
     * The block template.
     *
     * @var array
     */
    public $template = [
        'core/paragraph' => ['placeholder' => 'Quote text…'],
    ];

    /**
     * Fixture markup standing in for this block's InnerBlocks content on
     * the pattern library page (see App\View\Composers\PatternLibrary).
     *
     * @var string
     */
    public $exampleContent = '<p>Investing in safer streets and better cycle routes isn\'t just good for the environment — it\'s good for our high streets, our health, and our children\'s future.</p>';

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'attributionName' => $this->attributionName(),
            'attributionRole' => $this->attributionRole(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('quote');

        $fields
            ->addText('attribution_name', [
                'label' => 'Attribution name',
                'required' => 0,
            ])
            ->addText('attribution_role', [
                'label' => 'Attribution role',
                'required' => 0,
                'instructions' => 'e.g. "Cabinet Member for Sustainable Transport Delivery". Leave blank to hide.',
            ]);

        return $fields->build();
    }

    /**
     * Retrieve the attribution name.
     *
     * @return string
     */
    public function attributionName()
    {
        return get_field('attribution_name') ?: $this->example['attribution_name'];
    }

    /**
     * Retrieve the attribution role.
     *
     * @return string
     */
    public function attributionRole()
    {
        return get_field('attribution_role') ?: $this->example['attribution_role'];
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
