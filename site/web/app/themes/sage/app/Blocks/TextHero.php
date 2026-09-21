<?php

namespace App\Blocks;

use App\Fields\Copy;
use App\Fields\Heading;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class TextHero extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Text Hero';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A text-based hero that sits at the top of pages within the website.';

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
    public $icon = 'editor-textcolor';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'text',
        'hero',
        'heading',
        'tagline',
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
        'align_text' => true,
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
        'heading_text' => 'Get people cycling in Bristol',
        'heading_level' => 'h1',
        'heading_style' => 'match',
        'intro_text' => 'Example intro text for the hero — replace with real page content.',
        'intro_style' => 'standfirst',
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'heading' => $this->heading(),
            'intro' => $this->intro(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('text_hero');

        $fields->addPartial(Heading::class, [
            'name' => 'heading',
            'label' => 'Heading',
            'default_level' => 'h1',
            'required' => true,
        ]);

        $fields->addPartial(Copy::class, [
            'name' => 'intro',
            'label' => 'Intro text',
            'default_style' => 'standfirst',
        ]);

        return $fields->build();
    }

    /**
     * Retrieve the heading text/level/style.
     *
     * @return array
     */
    public function heading()
    {
        return [
            'text' => get_field('heading_text') ?: $this->example['heading_text'],
            'level' => get_field('heading_level') ?: $this->example['heading_level'],
            'style' => get_field('heading_style') ?: $this->example['heading_style'],
        ];
    }

    /**
     * Retrieve the intro text/style.
     *
     * @return array
     */
    public function intro()
    {
        return [
            'text' => get_field('intro_text') ?: $this->example['intro_text'],
            'style' => get_field('intro_style') ?: $this->example['intro_style'],
        ];
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
