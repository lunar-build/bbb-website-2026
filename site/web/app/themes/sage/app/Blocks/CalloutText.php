<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class CalloutText extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Callout Text';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'callout-text';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A single emphasised line of callout text within article content.';

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
    public $icon = 'info-outline';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'callout',
        'highlight',
        'note',
        'tip',
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
     * @var array
     */
    public $example = [
        'text' => 'Visit our online cycle planner to view these rides on your phone, whilst out on your ride.',
    ];

    public function with(): array
    {
        return [
            'text' => $this->text(),
        ];
    }

    public function fields(): array
    {
        $fields = Builder::make('callout_text');

        $fields
            ->addTextarea('text', [
                'label' => 'Callout text',
                'required' => 1,
                'rows' => 3,
                'new_lines' => 'br',
            ]);

        return $fields->build();
    }

    public function text()
    {
        return get_field('text') ?: $this->example['text'];
    }

    /**
     * @link https://developer.wordpress.org/block-editor/how-to-guides/enqueueing-assets-in-the-editor/#editor-content-scripts-and-styles
     */
    public function assets(array $block): void
    {
        //
    }
}
