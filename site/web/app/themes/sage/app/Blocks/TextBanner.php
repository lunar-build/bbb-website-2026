<?php

namespace App\Blocks;

use App\Blocks\Concerns\ResolvesFields;
use App\Fields\Heading;
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
    public $align_text = 'center';

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
        'align' => false,
        'align_text' => true,
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
        'heading_text' => '<p><strong>Explore</strong><br>ways to get a bike</p>',
        'heading_level' => 'h2',
        'heading_style' => 'h3',
        'body' => '<p>There are so many ways to get a bike, discover one that suits you best.</p>',
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'heading' => $this->heading(),
            'body' => $this->clean($this->resolveField('body', '')),
        ];
    }

    /**
     * The heading's text/level/style. The text's paragraphs become line breaks, because a
     * heading can't hold <p> elements.
     */
    protected function heading(): array
    {
        $html = $this->clean($this->resolveField('heading_text', ''));

        return [
            'text' => trim(preg_replace(['#</p>\s*<p>#i', '#</?p>#i'], ['<br>', ''], $html)),
            'level' => $this->resolveField('heading_level', 'h2'),
            'style' => $this->resolveField('heading_style', 'h3'),
        ];
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
            ->addPartial(Heading::class, [
                'name' => 'heading',
                'label' => 'Heading',
                'instructions' => 'The large text. Select words and use Bold or Italic to make them stand out (bold is shown in blue). Shift+Enter makes a line break. Align it with the toolbar; the background colour is set in the block sidebar.',
                'rich' => true,
                'allow_none' => true,
                'default_level' => 'h2',
                'default_style' => 'h3',
            ])
            ->addWysiwyg('body', [
                'label' => 'Body text',
                'instructions' => 'Optional. Normal-size text under the heading.',
                'tabs' => 'visual',
                'toolbar' => 'basic',
                'media_upload' => 0,
            ]);

        return $fields->build();
    }
}
