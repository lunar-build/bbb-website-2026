<?php

namespace App\Blocks;

use App\Blocks\Concerns\ResolvesFields;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class TextBanner extends Block
{
    use ResolvesFields;

    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Text Banner';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'text-banner';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A full-width statement of text on an optional coloured background, with an optional emphasised second line.';

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
    public $icon = 'editor-aligncenter';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'statement',
        'banner',
        'text',
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
        'instructor' => 'Cycle instructor',
        'cyclist' => 'Cyclist on bike',
    ];

    /**
     * The block preview example data.
     *
     * @var array
     */
    public $example = [
        'text' => '<p>Healthier, faster, more sustainable.<br><strong>Better by bike.</strong></p>',
        'alignment' => 'centre',
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'text' => $this->text(),
            'alignment' => $this->resolveField('alignment', 'centre'),
        ];
    }

    /**
     * The text, limited to paragraphs, line breaks, bold, italic and links.
     */
    public function text(): string
    {
        return wp_kses((string) $this->resolveField('text', ''), [
            'p' => [],
            'br' => [],
            'strong' => [],
            'b' => [],
            'em' => [],
            'a' => ['href' => [], 'target' => [], 'rel' => []],
        ]);
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('text_banner');

        $fields
            ->addWysiwyg('text', [
                'label' => 'Text',
                'instructions' => 'Select words and use Bold to make them stand out (shown bold and in blue). Shift+Enter makes a line break. The background colour is set in the block sidebar.',
                'tabs' => 'visual',
                'toolbar' => 'basic',
                'media_upload' => 0,
                'required' => 1,
            ])
            ->addButtonGroup('alignment', [
                'label' => 'Alignment',
                'choices' => [
                    'centre' => 'Centre',
                    'left' => 'Left',
                ],
                'default_value' => 'centre',
            ]);

        return $fields->build();
    }
}
