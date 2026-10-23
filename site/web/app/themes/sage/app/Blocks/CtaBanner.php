<?php

namespace App\Blocks;

use App\Blocks\Concerns\HasIllustrations;
use App\Blocks\Concerns\ResolvesFields;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class CtaBanner extends Block
{
    use HasIllustrations;
    use ResolvesFields;

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
    public $description = 'A call-to-action banner with a heading, body text, and link, in Centred/Left/Row/Slimline layouts.';

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
     * Layout variants to render stacked on the pattern-library page (see
     * App\View\Composers\PatternLibrary::render()) — each entry is merged
     * onto $example above, so only needs to override what differs.
     *
     * @var array
     */
    public $examples = [
        'Centred' => [],
        'Left' => ['layout' => 'left'],
        'Row' => ['layout' => 'row', 'image' => 'cyclist'],
        'Slimline' => ['layout' => 'slimline'],
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
                        'row' => 'Row',
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
                    'rows' => 4,
                ])
                ->addLink('link', [
                    'label' => 'Link',
                    'instructions' => 'Link text + URL for the CTA. Optional — leave empty for no button.',
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
                    'choices' => $this->illustrations('bmx', 'mountain_biker', 'instructor', 'cyclist'),
                    'default_value' => '',
                    'ui' => true,
                    'allow_null' => true,
                ])
                ->addSelect('image_right', [
                    'label' => 'Right illustration',
                    'choices' => $this->illustrations('bmx', 'mountain_biker', 'instructor', 'cyclist'),
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
                    [
                        [
                            'field' => 'layout',
                            'operator' => '==',
                            'value' => 'row',
                        ],
                    ],
                ],
            ])
                ->addSelect('image', [
                    'label' => 'Illustration',
                    'choices' => $this->illustrations('bmx', 'mountain_biker', 'instructor', 'cyclist'),
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
        return $this->resolveField('layout', 'centred');
    }

    /**
     * Retrieve the heading (empty on the Slimline layout, which has none).
     *
     * @return string
     */
    public function heading()
    {
        return get_field('heading') ?: ($this->layout() !== 'slimline' ? $this->exampleField('heading', '') : '');
    }

    /**
     * Retrieve the body text.
     *
     * @return string
     */
    public function body()
    {
        return $this->resolveField('body', '');
    }

    /**
     * Retrieve the link. Empty is valid — the CTA button is optional; on a
     * real saved post an empty field means no button, not the fixture link
     * (only pattern-library/editor preview falls back to that).
     *
     * @return array
     */
    public function link()
    {
        return $this->resolveField('link', ['title' => '', 'url' => '', 'target' => '']);
    }

    /**
     * Retrieve the Centred layout's left illustration URL.
     *
     * @return string|null
     */
    public function imageLeft()
    {
        return $this->illustrationUrl(get_field('image_left') ?: ($this->layout() === 'centred' ? $this->exampleField('image_left', '') : ''));
    }

    /**
     * Retrieve the Centred layout's right illustration URL.
     *
     * @return string|null
     */
    public function imageRight()
    {
        return $this->illustrationUrl(get_field('image_right') ?: ($this->layout() === 'centred' ? $this->exampleField('image_right', '') : ''));
    }

    /**
     * Retrieve the Left/Row layouts' illustration URL.
     *
     * @return string|null
     */
    public function image()
    {
        $isLeftLayout = in_array($this->layout(), ['left', 'row'], true);

        return $this->illustrationUrl(get_field('image') ?: ($isLeftLayout ? $this->exampleField('image', '') : ''));
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
