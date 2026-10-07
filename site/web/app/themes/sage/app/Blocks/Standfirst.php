<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class Standfirst extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Standfirst';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'standfirst';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A short lead paragraph shown above the article body.';

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
    public $icon = 'editor-paragraph';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'standfirst',
        'lead',
        'intro',
        'summary',
    ];

    /**
     * The block post type allow list.
     *
     * @var array
     */
    public $post_types = ['post'];

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
        'multiple' => false,
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
        'text' => 'Work on three walking, wheeling and cycling routes to help people get across Bath safely and more easily is set to get under way this summer and autumn.',
    ];

    public function with(): array
    {
        return [
            'text' => $this->text(),
        ];
    }

    public function fields(): array
    {
        $fields = Builder::make('standfirst');

        $fields->addTextarea('text', [
            'label' => 'Standfirst',
            'instructions' => 'A short lead paragraph, 1-2 sentences.',
            'rows' => 3,
            'new_lines' => false,
            'required' => 1,
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
