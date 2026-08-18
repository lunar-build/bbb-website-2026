<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class FeatureCard extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Feature Card';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'feature-card';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A flexible image card for events, news listings, partner logos, and stat/route summaries.';

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
    public $icon = 'id-alt';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'card',
        'feature',
        'cta',
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
    public $styles = ['light', 'dark'];

    /**
     * The block preview example data.
     *
     * @var array
     */
    public $example = [
        'link' => [
            'title' => 'Get involved',
            'url' => 'https://betterbybike.info/get-involved/',
            'target' => '',
        ],
    ];

    /**
     * The block template.
     *
     * @var array
     */
    public $template = [
        'core/heading' => ['placeholder' => 'Title', 'level' => 3],
        'core/paragraph' => ['placeholder' => 'Body text (optional — delete for cards without body copy)…'],
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'image' => $this->image(),
            'date' => $this->date(),
            'stats' => $this->stats(),
            'link' => $this->link(),
            'ctaStyle' => $this->ctaStyle(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('feature_card');

        $fields
            ->addTab('Media')
                ->addImage('image', [
                    'label' => 'Image',
                    'instructions' => 'Large image shown at the top of the card.',
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'required' => 1,
                ])
            ->addTab('Content')
                ->addDatePicker('date', [
                    'label' => 'Date',
                    'instructions' => 'Optional. Leave blank to hide (e.g. for partner/route cards).',
                    'display_format' => 'jS F Y',
                    'return_format' => 'jS F Y',
                ])
            ->addTab('Stats')
                ->addRepeater('stats', [
                    'label' => 'Stats',
                    'instructions' => 'Optional label/value rows (e.g. Difficulty, Time needed, Distance). Leave empty to omit.',
                    'button_label' => 'Add stat',
                    'min' => 0,
                    'layout' => 'block',
                ])
                    ->addText('label', [
                        'label' => 'Label',
                        'required' => 1,
                    ])
                    ->addText('value', [
                        'label' => 'Value',
                        'required' => 1,
                    ])
                    ->addTrueFalse('show_as_pill', [
                        'label' => 'Show as pill',
                        'instructions' => 'Renders the value as a coloured wa-badge instead of plain text.',
                        'ui' => 1,
                        'default_value' => 0,
                    ])
                    ->addSelect('pill_variant', [
                        'label' => 'Pill colour',
                        'instructions' => 'Maps to a wa-badge variant.',
                        'choices' => [
                            'success' => 'Success (green) — e.g. Easy',
                            'warning' => 'Warning (amber) — e.g. Moderate',
                            'danger' => 'Danger (red)',
                            'neutral' => 'Neutral (grey)',
                            'brand' => 'Brand',
                        ],
                        'default_value' => 'success',
                        'conditional_logic' => [
                            [
                                [
                                    'field' => 'show_as_pill',
                                    'operator' => '==',
                                    'value' => '1',
                                ],
                            ],
                        ],
                    ])
                ->endRepeater()
            ->addTab('Call to Action')
                ->addLink('link', [
                    'label' => 'Link',
                    'instructions' => 'URL the card links to. Leave empty for no link at all.',
                ])
                ->addSelect('cta_style', [
                    'label' => 'CTA style',
                    'instructions' => 'How the link is presented. Ignored if the Link field above is empty.',
                    'choices' => [
                        'button' => 'Full button (visible label)',
                        'icon' => 'Icon only (arrow, no label)',
                        'none' => 'No visible CTA — card is still fully clickable via Link',
                    ],
                    'default_value' => 'button',
                ]);

        return $fields->build();
    }

    /**
     * Retrieve the image.
     *
     * @return array|null
     */
    public function image()
    {
        return get_field('image');
    }

    /**
     * Retrieve the date.
     *
     * @return string|null
     */
    public function date()
    {
        return get_field('date') ?: null;
    }

    /**
     * Retrieve the stats.
     *
     * @return array
     */
    public function stats()
    {
        return get_field('stats') ?: [];
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
     * Retrieve the CTA style.
     *
     * @return string
     */
    public function ctaStyle()
    {
        return get_field('cta_style') ?: 'button';
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
