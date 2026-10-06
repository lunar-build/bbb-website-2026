<?php

namespace App\Blocks;

use Illuminate\Support\Facades\Vite;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class ImageBlock extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Image Block';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A single image, with either an optional caption or an info box underneath — covers both the "Image with optional caption" and "Image with info box" Figma variants via one toggle. The info box itself has a further toggle between an icon-rows/bullet-list layout and a freeform rich-text layout.';

    /**
     * The block category.
     *
     * @var string
     */
    public $category = 'media';

    /**
     * The block icon.
     *
     * @var string|array
     */
    public $icon = 'format-image';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'image',
        'caption',
        'info box',
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
        'content_type' => 'info_box',
        'caption' => 'An optional caption goes here.',
        'info_box_type' => 'columns',
        'info_box_rows_heading' => 'Next session',
        'info_box_rows' => [
            ['icon' => 'calendar', 'text' => 'Wednesday 19th August 2026'],
            ['icon' => 'clock', 'text' => '9.45am'],
        ],
        'info_box_heading' => 'Price',
        'info_box_bullets' => [
            ['text' => '£5 per child and per accompanying adult'],
            ['text' => '£7 per solo adult'],
            ['text' => 'Carers and under 2s FREE'],
            ['text' => 'This includes hire of our cycles during that session on our site.'],
        ],
        'info_box_content' => '<p>Rich text content goes here.</p>',
    ];

    /**
     * Pattern-library variants — each stacked under its label, rendered
     * with these overrides shallow-merged onto $example. Covers the three
     * Figma-distinct states: plain caption, info box with icon rows +
     * bullet list, and info box with freeform rich text.
     *
     * @var array
     */
    public $examples = [
        'Caption' => ['content_type' => 'caption'],
        'Info box — icon rows + bullet list' => ['content_type' => 'info_box', 'info_box_type' => 'columns'],
        'Info box — rich text' => ['content_type' => 'info_box', 'info_box_type' => 'wysiwyg'],
    ];

    /**
     * Fallback example data requiring a non-constant expression (Vite::asset).
     *
     * @return array
     */
    public function example(): array
    {
        return [
            'image' => ['url' => Vite::asset('resources/images/placeholder/pattern-placeholder.svg'), 'alt' => ''],
        ];
    }

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'image' => $this->image(),
            'contentType' => $this->contentType(),
            'caption' => $this->caption(),
            'infoBoxType' => $this->infoBoxType(),
            'infoBoxRowsHeading' => $this->infoBoxRowsHeading(),
            'infoBoxRows' => $this->infoBoxRows(),
            'infoBoxHeading' => $this->infoBoxHeading(),
            'infoBoxBullets' => $this->infoBoxBullets(),
            'infoBoxContent' => $this->infoBoxContent(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('image_block');

        $infoBoxCondition = [
            [
                [
                    'field' => 'content_type',
                    'operator' => '==',
                    'value' => 'info_box',
                ],
            ],
        ];

        $infoBoxColumnsCondition = [
            [
                [
                    'field' => 'content_type',
                    'operator' => '==',
                    'value' => 'info_box',
                ],
                [
                    'field' => 'info_box_type',
                    'operator' => '==',
                    'value' => 'columns',
                ],
            ],
        ];

        $infoBoxWysiwygCondition = [
            [
                [
                    'field' => 'content_type',
                    'operator' => '==',
                    'value' => 'info_box',
                ],
                [
                    'field' => 'info_box_type',
                    'operator' => '==',
                    'value' => 'wysiwyg',
                ],
            ],
        ];

        $fields
            ->addImage('image', [
                'label' => 'Image',
                'required' => true,
                'return_format' => 'array',
                'preview_size' => 'large',
            ])
            ->addSelect('content_type', [
                'label' => 'Content underneath image',
                'choices' => [
                    'caption' => 'Caption',
                    'info_box' => 'Info box',
                ],
                'default_value' => 'caption',
                'ui' => true,
            ])
            ->addTextarea('caption', [
                'label' => 'Caption',
                'instructions' => 'Optional.',
                'rows' => 2,
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'content_type',
                            'operator' => '==',
                            'value' => 'caption',
                        ],
                    ],
                ],
            ])
            ->addSelect('info_box_type', [
                'label' => 'Info box layout',
                'choices' => [
                    'columns' => 'Icon rows + bullet list',
                    'wysiwyg' => 'Rich text',
                ],
                'default_value' => 'columns',
                'ui' => true,
                'conditional_logic' => $infoBoxCondition,
            ])
            ->addText('info_box_rows_heading', [
                'label' => 'Icon rows heading',
                'instructions' => 'e.g. "Next session".',
                'required' => 0,
                'conditional_logic' => $infoBoxColumnsCondition,
            ])
            ->addRepeater('info_box_rows', [
                'label' => 'Icon rows',
                'button_label' => 'Add row',
                'min' => 0,
                'layout' => 'table',
                'conditional_logic' => $infoBoxColumnsCondition,
            ])
                ->addSelect('icon', [
                    'label' => 'Icon',
                    'choices' => svg_icon_choices(),
                    'required' => 1,
                ])
                ->addText('text', [
                    'label' => 'Text',
                    'required' => 1,
                ])
            ->endRepeater()
            ->addText('info_box_heading', [
                'label' => 'Bullet list heading',
                'instructions' => 'e.g. "Price".',
                'required' => 0,
                'conditional_logic' => $infoBoxColumnsCondition,
            ])
            ->addRepeater('info_box_bullets', [
                'label' => 'Bullet list items',
                'button_label' => 'Add item',
                'min' => 0,
                'layout' => 'table',
                'conditional_logic' => $infoBoxColumnsCondition,
            ])
                ->addText('text', [
                    'label' => 'Item text',
                    'required' => 1,
                ])
            ->endRepeater()
            ->addWysiwyg('info_box_content', [
                'label' => 'Content',
                'required' => 0,
                'tabs' => 'visual',
                'media_upload' => 0,
                'toolbar' => 'full',
                'conditional_logic' => $infoBoxWysiwygCondition,
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
        return get_field('image') ?: $this->example['image'];
    }

    /**
     * Retrieve the content type ('caption' or 'info_box').
     *
     * @return string
     */
    public function contentType()
    {
        return get_field('content_type') ?: $this->example['content_type'];
    }

    /**
     * Retrieve the caption. Empty is valid — the caption is optional; on a
     * real saved post an empty field means no caption, not the fixture
     * text (only pattern-library/editor preview falls back to that).
     *
     * @return string
     */
    public function caption()
    {
        return get_field('caption') ?: ($this->preview ? ($this->example['caption'] ?? '') : '');
    }

    /**
     * Retrieve the info box layout ('columns' or 'wysiwyg').
     *
     * @return string
     */
    public function infoBoxType()
    {
        return get_field('info_box_type') ?: $this->example['info_box_type'];
    }

    /**
     * Retrieve the icon rows column heading. Empty is valid — the heading
     * is optional; only pattern-library/editor preview falls back to the
     * fixture text.
     *
     * @return string
     */
    public function infoBoxRowsHeading()
    {
        return get_field('info_box_rows_heading') ?: ($this->preview ? ($this->example['info_box_rows_heading'] ?? '') : '');
    }

    /**
     * Retrieve the icon rows.
     *
     * @return array
     */
    public function infoBoxRows()
    {
        return get_field('info_box_rows') ?: $this->example['info_box_rows'];
    }

    /**
     * Retrieve the bullet list heading. Empty is valid — the heading is
     * optional; only pattern-library/editor preview falls back to the
     * fixture text.
     *
     * @return string
     */
    public function infoBoxHeading()
    {
        return get_field('info_box_heading') ?: ($this->preview ? ($this->example['info_box_heading'] ?? '') : '');
    }

    /**
     * Retrieve the bullet list items.
     *
     * @return array
     */
    public function infoBoxBullets()
    {
        return get_field('info_box_bullets') ?: $this->example['info_box_bullets'];
    }

    /**
     * Retrieve the rich-text info box content.
     *
     * @return string
     */
    public function infoBoxContent()
    {
        return get_field('info_box_content') ?: $this->example['info_box_content'];
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
