<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class FaqAccordion extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'FAQ Accordion';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'faq-accordion';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A list of expandable question and answer items.';

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
        'faq',
        'accordion',
        'questions',
        'answers',
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
        'heading' => 'Frequently asked questions',
        'items' => [
            [
                'question' => 'How do I borrow a bike?',
                'answer' => 'Sign up online, choose a scheme near you, and pick up your bike from one of our local hubs.',
            ],
            [
                'question' => 'How long can I keep the bike for?',
                'answer' => 'Loan periods vary by scheme, but most run for up to a month at a time.',
            ],
            [
                'question' => 'What if something goes wrong with the bike?',
                'answer' => 'Get in touch with your local scheme coordinator and we\'ll arrange a repair or replacement.',
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
        $fields = Builder::make('faq_accordion');

        $fields
            ->addText('heading', [
                'label' => 'Heading',
            ])
            ->addRepeater('items', [
                'label' => 'Questions',
                'button_label' => 'Add question',
                'min' => 1,
                'layout' => 'block',
            ])
                ->addText('question', [
                    'label' => 'Question',
                    'required' => 1,
                ])
                ->addTextarea('answer', [
                    'label' => 'Answer',
                    'rows' => 3,
                    'new_lines' => 'br',
                    'required' => 1,
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
     * Retrieve the FAQ items.
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
