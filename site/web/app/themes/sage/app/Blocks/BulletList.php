<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class BulletList extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Bullet List';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'bullet-list';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A styled bullet list with an optional heading, for use within article content.';

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
    public $icon = 'editor-ul';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'bullet',
        'list',
        'ul',
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
        'heading' => 'Example of non-urgent',
        'items' => [
            ['text' => 'Missing cycle signage'],
            ['text' => 'Road markings'],
            ['text' => 'Potholes'],
            ['text' => 'Cycle lane needs resurfacing'],
            ['text' => 'Cutting back brambles'],
        ],
    ];

    public function with(): array
    {
        return [
            'heading' => $this->heading(),
            'items' => $this->items(),
        ];
    }

    public function fields(): array
    {
        $fields = Builder::make('bullet_list');

        $fields
            ->addText('heading', [
                'label' => 'Heading',
                'instructions' => 'Optional heading shown above the list.',
                'required' => 0,
            ])
            ->addRepeater('items', [
                'label' => 'List items',
                'button_label' => 'Add item',
                'min' => 1,
                'layout' => 'table',
            ])
                ->addText('text', [
                    'label' => 'Item text',
                    'required' => 1,
                ])
            ->endRepeater();

        return $fields->build();
    }

    public function heading()
    {
        return get_field('heading') ?: $this->example['heading'];
    }

    public function items()
    {
        return get_field('items') ?: $this->example['items'];
    }

    /**
     * @link https://developer.wordpress.org/block-editor/how-to-guides/enqueueing-assets-in-the-editor/#editor-content-scripts-and-styles
     */
    public function assets(array $block): void
    {
        //
    }
}
