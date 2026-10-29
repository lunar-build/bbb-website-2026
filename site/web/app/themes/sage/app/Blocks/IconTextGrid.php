<?php

namespace App\Blocks;

use App\Blocks\Concerns\ResolvesFields;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class IconTextGrid extends Block
{
    use ResolvesFields;

    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Icon Text Grid';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'icon-text-grid';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A grid of icons, each with body text underneath.';

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
    public $icon = 'grid-view';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'icon',
        'text',
        'grid',
        'list',
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
            'background' => true,
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
        'text_align' => 'left',
        'items' => [
            [
                'icon' => 'cycling-route',
                'body' => 'Find a route that suits you. Discover the shortest and safest cycle routes to get you where you want to go.',
            ],
            [
                'icon' => 'bike',
                'body' => 'Borrow one of our free electric, hybrid or folding bikes for up to a month.',
            ],
            [
                'icon' => 'cycling-person',
                'body' => 'Training is available for adults, children, and people with learning and physical difficulties.',
            ],
        ],
    ];

    public function with(): array
    {
        return [
            'items' => $this->items(),
            'textAlign' => $this->textAlign(),
        ];
    }

    public function fields(): array
    {
        $fields = Builder::make('icon_text_grid');

        $fields
            ->addRadio('text_align', [
                'label' => 'Text alignment',
                'choices' => [
                    'left' => 'Left',
                    'center' => 'Centre',
                ],
                'default_value' => 'left',
                'layout' => 'horizontal',
                'return_format' => 'value',
            ])
            ->addRepeater('items', [
                'label' => 'Items',
                'button_label' => 'Add item',
                'min' => 0,
                'layout' => 'block',
            ])
                ->addSelect('icon', [
                    'label' => 'Icon',
                    'choices' => svg_icon_choices(),
                    'required' => 1,
                ])
                ->addTextarea('body', [
                    'label' => 'Body text',
                    'rows' => 3,
                    'required' => 1,
                ])
            ->endRepeater();

        return $fields->build();
    }

    public function items()
    {
        return $this->resolveField('items', []);
    }

    public function textAlign()
    {
        return $this->resolveField('text_align', 'left') === 'center' ? 'center' : 'left';
    }

    /**
     * @link https://developer.wordpress.org/block-editor/how-to-guides/enqueueing-assets-in-the-editor/#editor-content-scripts-and-styles
     */
    public function assets(array $block): void
    {
        //
    }
}
