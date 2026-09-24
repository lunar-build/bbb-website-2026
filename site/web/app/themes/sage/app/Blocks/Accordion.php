<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class Accordion extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Accordion';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'accordion';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A list of expandable, collapsible content items.';

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
    public $icon = 'editor-help';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'accordion',
        'expand',
        'collapse',
        'faq',
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
        'heading' => 'Frequently asked questions',
        'items' => [
            [
                'title' => 'How do I borrow a bike?',
                'content' => 'Sign up online, choose a scheme near you, and pick up your bike from one of our local hubs.',
            ],
            [
                'title' => 'How long can I keep the bike for?',
                'content' => 'Loan periods vary by scheme, but most run for up to a month at a time.',
            ],
            [
                'title' => 'What if something goes wrong with the bike?',
                'content' => 'Get in touch with your local scheme coordinator and we\'ll arrange a repair or replacement.',
            ],
        ],
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'heading' => $this->heading(),
            'items' => $this->items(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('accordion');

        $fields
            ->addText('heading', [
                'label' => 'Heading',
            ])
            ->addRepeater('items', [
                'label' => 'Items',
                'button_label' => 'Add item',
                'min' => 1,
                'layout' => 'block',
            ])
                ->addText('title', [
                    'label' => 'Title',
                    'required' => 1,
                ])
                ->addWysiwyg('content', [
                    'label' => 'Content',
                    'required' => 1,
                    'tabs' => 'visual',
                    'media_upload' => 0,
                    'toolbar' => 'basic',
                ])
            ->endRepeater();

        return $fields->build();
    }

    /**
     * Retrieve the heading.
     *
     * @return string
     */
    public function heading()
    {
        return get_field('heading') ?: $this->example['heading'];
    }

    /**
     * Retrieve the accordion items.
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
