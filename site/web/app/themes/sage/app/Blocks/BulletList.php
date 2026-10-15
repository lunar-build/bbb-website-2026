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
        // Lists usually sit within running text — no gap by default; editors
        // can add one with the spacing controls.
        'padding' => [
            'top' => '0',
            'bottom' => '0',
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
        'list_style' => 'bullets',
        'items' => [
            ['text' => 'Missing cycle signage', 'level' => 0],
            ['text' => 'Road markings', 'level' => 0],
            ['text' => 'Potholes', 'level' => 0],
            ['text' => 'Cycle lane needs resurfacing', 'level' => 0],
            ['text' => 'Cutting back brambles', 'level' => 0],
        ],
    ];

    /**
     * Component combinations to render stacked on the pattern-library page.
     *
     * @var array
     */
    public $examples = [
        'Bullets' => [],
        'Numbers, nested' => [
            'heading' => 'How to report a fault',
            'list_style' => 'numbers',
            'items' => [
                ['text' => 'Find the pump on the map', 'level' => 0],
                ['text' => 'Note its <strong>location</strong>', 'level' => 0],
                ['text' => 'Street name', 'level' => 1],
                ['text' => 'Nearest landmark', 'level' => 1],
                ['text' => 'Fill in the fault form', 'level' => 0],
            ],
        ],
    ];

    public function with(): array
    {
        return [
            'heading' => $this->heading(),
            'ordered' => $this->listStyle() === 'numbers',
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
            ->addButtonGroup('list_style', [
                'label' => 'List style',
                'choices' => [
                    'bullets' => 'Bullets',
                    'numbers' => 'Numbers',
                ],
                'default_value' => 'bullets',
            ])
            ->addRepeater('items', [
                'label' => 'List items',
                'button_label' => 'Add item',
                'min' => 1,
                'layout' => 'table',
            ])
                ->addText('text', [
                    'label' => 'Item text',
                    'instructions' => 'Plain text. For bold, italic or a link, wrap words in &lt;strong&gt;, &lt;em&gt; or &lt;a href="…"&gt; tags.',
                    'required' => 1,
                ])
                ->addSelect('level', [
                    'label' => 'Indent',
                    'instructions' => 'Indented items nest under the item above.',
                    'choices' => [
                        0 => 'None',
                        1 => '1 level',
                        2 => '2 levels',
                    ],
                    'default_value' => 0,
                    'wrapper' => ['width' => '20'],
                ])
            ->endRepeater();

        return $fields->build();
    }

    public function heading()
    {
        return get_field('heading') ?: (shows_example($this->preview) ? $this->example['heading'] : '');
    }

    public function listStyle()
    {
        return get_field('list_style') ?: (shows_example($this->preview) ? $this->example['list_style'] : 'bullets');
    }

    public function items()
    {
        return get_field('items') ?: (shows_example($this->preview) ? $this->example['items'] : []);
    }

    /**
     * @link https://developer.wordpress.org/block-editor/how-to-guides/enqueueing-assets-in-the-editor/#editor-content-scripts-and-styles
     */
    public function assets(array $block): void
    {
        //
    }
}
