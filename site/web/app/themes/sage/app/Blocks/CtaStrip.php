<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class CtaStrip extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'CTA Strip';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'cta-strip';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A reusable bordered call-to-action banner with a title, body text, and link.';

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
    public $icon = 'megaphone';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'cta',
        'banner',
        'call to action',
        'link',
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
     * @var array
     */
    public $example = [
        'link' => [
            'title' => 'Find out more',
            'url' => 'https://betterbybike.info/schemes-and-initiatives/loan-a-bike-scheme/',
            'target' => '',
        ],
    ];

    /**
     * The block template.
     *
     * @var array
     */
    public $template = [
        'core/heading' => ['placeholder' => 'Heading', 'level' => 3],
        'core/paragraph' => ['placeholder' => 'Body text…'],
    ];

    /**
     * Fixture markup standing in for this block's InnerBlocks content on
     * the pattern library page (see App\View\Composers\PatternLibrary).
     *
     * @var string
     */
    public $exampleContent = '<h3>Try our loan a bike scheme</h3><p>Example body copy for the strip — replace with real content.</p>';

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'link' => $this->link(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('cta_strip');

        $fields
            ->addLink('link', [
                'label' => 'Link',
                'instructions' => 'Link text + URL for the CTA.',
            ]);

        return $fields->build();
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
