<?php

namespace App\Blocks;

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
    public $mode = 'preview';

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
        'core/heading' => ['placeholder' => 'Hello World', 'level' => 1],
        'core/paragraph' => ['placeholder' => 'Welcome to the Text Hero block.', 'fontSize' => 'lg'],
    ];

    /**
     * Fixture markup standing in for this block's InnerBlocks content on
     * the pattern library page (see App\View\Composers\PatternLibrary).
     *
     * @var string
     */
    public $exampleContent = '<h1>Get people cycling in Bristol</h1><p>Example intro text for the hero — replace with real page content.</p>';

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('text_hero');
        return $fields->build();
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
