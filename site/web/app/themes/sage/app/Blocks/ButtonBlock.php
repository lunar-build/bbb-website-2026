<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class ButtonBlock extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Button';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'button';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A single call-to-action button in one of three styles (yellow, charcoal, outlined), large or small, aligned left, centre or right. Outlined is only designed in the small size.';

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
    public $icon = 'admin-links';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'button',
        'cta',
        'link',
        'call to action',
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
     * The default block spacing — Small, not the usual Large: a lone button
     * between paragraphs shouldn't add 8rem of space by default.
     *
     * @var array
     */
    public $spacing = [
        'padding' => [
            'top' => 'var:preset|spacing|small',
            'bottom' => 'var:preset|spacing|small',
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
        'link' => ['title' => 'Find out more', 'url' => '#', 'target' => ''],
        'button_style' => 'brand',
        'button_size' => 'large',
        'button_align' => 'left',
    ];

    /**
     * Variants stacked on the pattern-library page (each is merged onto
     * $example). Outlined is small-only — it has no large design.
     *
     * @var array
     */
    public $examples = [
        'Yellow — large' => [],
        'Charcoal — large' => ['button_style' => 'neutral'],
        'Yellow — small' => ['button_size' => 'small'],
        'Charcoal — small' => ['button_style' => 'neutral', 'button_size' => 'small'],
        'Outlined — small' => ['button_style' => 'outlined', 'button_size' => 'small'],
        'Centre aligned' => ['button_align' => 'center'],
        'Right aligned' => ['button_align' => 'right'],
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        $style = $this->style();

        return [
            'link' => $this->link(),
            'style' => $style,
            'size' => $this->size($style),
            'align' => $this->alignment(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('button');

        $fields
            ->addLink('link', [
                'label' => 'Link',
                'instructions' => 'The link\'s title is the button label.',
                'required' => 1,
            ])
            ->addSelect('button_style', [
                'label' => 'Style',
                'choices' => [
                    'brand' => 'Yellow',
                    'neutral' => 'Charcoal',
                    'outlined' => 'Outlined (small only)',
                ],
                'default_value' => 'brand',
                'ui' => true,
            ])
            ->addSelect('button_size', [
                'label' => 'Size',
                'choices' => [
                    'large' => 'Large',
                    'small' => 'Small',
                ],
                'default_value' => 'large',
                'ui' => true,
                'conditional_logic' => [[['field' => 'button_style', 'operator' => '!=', 'value' => 'outlined']]],
            ])
            ->addButtonGroup('button_align', [
                'label' => 'Alignment',
                'choices' => [
                    'left' => 'Left',
                    'center' => 'Centre',
                    'right' => 'Right',
                ],
                'default_value' => 'left',
            ]);

        return $fields->build();
    }

    /**
     * Retrieve the link, always with a usable title.
     *
     * @return array
     */
    public function link()
    {
        $link = normalize_link(get_field('link') ?: $this->example['link']);
        $link['title'] = $link['title'] ?: 'Find out more';

        return $link;
    }

    /**
     * Retrieve the button style: brand, neutral or outlined.
     *
     * @return string
     */
    public function style()
    {
        $style = get_field('button_style') ?: $this->example['button_style'];

        return in_array($style, ['brand', 'neutral', 'outlined'], true) ? $style : 'brand';
    }

    /**
     * Retrieve the button size. Outlined only exists in small.
     *
     * @return string
     */
    public function size(string $style)
    {
        if ($style === 'outlined') {
            return 'small';
        }

        $size = get_field('button_size') ?: $this->example['button_size'];

        return $size === 'small' ? 'small' : 'large';
    }

    /**
     * Retrieve the horizontal alignment: left, center or right.
     *
     * @return string
     */
    public function alignment()
    {
        $align = get_field('button_align') ?: $this->example['button_align'];

        return in_array($align, ['left', 'center', 'right'], true) ? $align : 'left';
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
