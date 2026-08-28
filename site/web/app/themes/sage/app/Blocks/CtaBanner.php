<?php

namespace App\Blocks;

use Illuminate\Support\Facades\Vite;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class CtaBanner extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'CTA Banner';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'cta-banner';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A call-to-action banner with a heading, body text, and link, in Centred/Left/Slimline layouts.';

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
    public $icon = 'megaphone';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'cta',
        'banner',
        'call to action',
        'link',
        'layout',
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
     * Illustration choices shared by every layout's image select(s) — a
     * fixed, developer-controlled set (not an open media upload), so the
     * front end always renders a bundled resources/images/illustrations/*
     * asset rather than an arbitrary client-uploaded image.
     *
     * @var array
     */
    protected $illustrationChoices = [
        '' => 'None',
        'bmx' => 'BMX rider',
        'mountain_biker' => 'Mountain biker',
    ];

    /**
     * The block preview example data.
     *
     * @var array
     */
    public $example = [
        'layout' => 'centred',
        'heading' => 'I bike it, I like it!',
        'body' => 'Get inspired to start cycling or remind yourself about all the wonderful benefits of cycling – from your own health to the positive impact on the environment. Read more about why cycle!',
        'link' => [
            'title' => 'Why cycle',
            'url' => 'https://betterbybike.info/why-cycle/',
            'target' => '',
        ],
        'image_left' => 'bmx',
        'image_right' => 'mountain_biker',
        'image' => 'mountain_biker',
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'layout' => $this->layout(),
            'heading' => $this->heading(),
            'body' => $this->body(),
            'link' => $this->link(),
            'imageLeft' => $this->imageLeft(),
            'imageRight' => $this->imageRight(),
            'image' => $this->image(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('cta_banner');

        $fields
            ->addTab('Content')
                ->addSelect('layout', [
                    'label' => 'Layout',
                    'choices' => [
                        'centred' => 'Centred',
                        'left' => 'Left',
                        'slimline' => 'Slimline',
                    ],
                    'default_value' => 'centred',
                    'ui' => true,
                ])
                ->addText('heading', [
                    'label' => 'Heading',
                    'conditional_logic' => [
                        [
                            [
                                'field' => 'layout',
                                'operator' => '!=',
                                'value' => 'slimline',
                            ],
                        ],
                    ],
                ])
                ->addTextarea('body', [
                    'label' => 'Body text',
                    'required' => true,
                    'rows' => 4,
                ])
                ->addLink('link', [
                    'label' => 'Link',
                    'instructions' => 'Link text + URL for the CTA.',
                    'required' => true,
                ])
            ->addTab('Centred layout images', [
                'conditional_logic' => [
                    [
                        [
                            'field' => 'layout',
                            'operator' => '==',
                            'value' => 'centred',
                        ],
                    ],
                ],
            ])
                ->addSelect('image_left', [
                    'label' => 'Left illustration',
                    'choices' => $this->illustrationChoices,
                    'default_value' => '',
                    'ui' => true,
                    'allow_null' => true,
                ])
                ->addSelect('image_right', [
                    'label' => 'Right illustration',
                    'choices' => $this->illustrationChoices,
                    'default_value' => '',
                    'ui' => true,
                    'allow_null' => true,
                ])
            ->addTab('Left layout image', [
                'conditional_logic' => [
                    [
                        [
                            'field' => 'layout',
                            'operator' => '==',
                            'value' => 'left',
                        ],
                    ],
                ],
            ])
                ->addSelect('image', [
                    'label' => 'Illustration',
                    'choices' => $this->illustrationChoices,
                    'default_value' => '',
                    'ui' => true,
                    'allow_null' => true,
                ])
        ;

        return $fields->build();
    }

    /**
     * Retrieve the layout.
     *
     * @return string
     */
    public function layout()
    {
        return get_field('layout') ?: $this->example['layout'];
    }

    /**
     * Retrieve the heading (empty on the Slimline layout, which has none).
     *
     * @return string
     */
    public function heading()
    {
        return get_field('heading') ?: ($this->layout() !== 'slimline' ? $this->example['heading'] : '');
    }

    /**
     * Retrieve the body text.
     *
     * @return string
     */
    public function body()
    {
        return get_field('body') ?: $this->example['body'];
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
     * Retrieve the Centred layout's left illustration URL.
     *
     * @return string|null
     */
    public function imageLeft()
    {
        return $this->illustrationUrl(get_field('image_left') ?: ($this->layout() === 'centred' ? $this->example['image_left'] : ''));
    }

    /**
     * Retrieve the Centred layout's right illustration URL.
     *
     * @return string|null
     */
    public function imageRight()
    {
        return $this->illustrationUrl(get_field('image_right') ?: ($this->layout() === 'centred' ? $this->example['image_right'] : ''));
    }

    /**
     * Retrieve the Left layout's illustration URL.
     *
     * @return string|null
     */
    public function image()
    {
        return $this->illustrationUrl(get_field('image') ?: ($this->layout() === 'left' ? $this->example['image'] : ''));
    }

    /**
     * Resolve an illustration select value to its bundled asset URL.
     *
     * @return string|null
     */
    protected function illustrationUrl(?string $key)
    {
        if (! $key) {
            return null;
        }

        $file = str_replace('_', '-', $key);

        return Vite::asset("resources/images/illustrations/{$file}.png");
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
