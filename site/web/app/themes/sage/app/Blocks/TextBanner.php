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
        'heading' => '<p><strong>Explore</strong><br>ways to get a bike</p>',
        'heading_level' => 'h2',
        'body' => '<p>There are so many ways to get a bike, discover one that suits you best.</p>',
        'alignment' => 'centre',
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'heading' => $this->heading(),
            'headingTag' => $this->headingTag(),
            'body' => $this->clean($this->resolveField('body', '')),
            'alignment' => $this->resolveField('alignment', 'centre'),
        ];
    }

    /**
     * The heading's inline markup: its paragraphs become line breaks, because a heading
     * can't hold <p> elements.
     */
    protected function heading(): string
    {
        $html = $this->clean($this->resolveField('heading', ''));

        return trim(preg_replace(['#</p>\s*<p>#i', '#</?p>#i'], ['<br>', ''], $html));
    }

    /**
     * The tag the heading is output as: h2 to h4, or a plain div when it's only styled text.
     */
    protected function headingTag(): string
    {
        $level = $this->resolveField('heading_level', 'h2');

        return in_array($level, ['h2', 'h3', 'h4'], true) ? $level : 'div';
    }

    /**
     * Rich text limited to paragraphs, line breaks, bold, italic and links.
     */
    protected function clean(string $html): string
    {
        return wp_kses($html, [
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
            ->addWysiwyg('heading', [
                'label' => 'Heading',
                'instructions' => 'The large text. Select words and use Bold to make them stand out (shown bold and in blue). Shift+Enter makes a line break. The background colour is set in the block sidebar.',
                'tabs' => 'visual',
                'toolbar' => 'basic',
                'media_upload' => 0,
            ])
            ->addSelect('heading_level', [
                'label' => 'Heading level',
                'instructions' => 'Use a heading level when the text introduces the content below it. Choose "Not a heading" for a standalone statement, so screen readers and the page outline don\'t treat it as a section.',
                'choices' => [
                    'h2' => 'Heading 2',
                    'h3' => 'Heading 3',
                    'h4' => 'Heading 4',
                    'none' => 'Not a heading (just styled text)',
                ],
                'default_value' => 'h2',
                'ui' => true,
            ])
            ->addWysiwyg('body', [
                'label' => 'Body text',
                'instructions' => 'Optional. Normal-size text under the heading.',
                'tabs' => 'visual',
                'toolbar' => 'basic',
                'media_upload' => 0,
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
