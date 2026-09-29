<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class QuoteCarousel extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Quote Carousel';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'quote-carousel';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A carousel of pull-quote slides, each with a quote, bolded stat, and attribution/event name.';

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
    public $icon = 'slides';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'quote',
        'carousel',
        'slider',
        'stat',
        'testimonial',
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
     * Figma "Property 1" states (node 7:1982 "Default" / 19:582 "Variant2")
     * share the same content model — only a style toggle, matching how
     * Quote.php merged its Short/Long variants into one block rather than
     * splitting into two. Variant2's dashed border/light-blue background
     * was a one-off designer slip (not repeated elsewhere in the file) —
     * the real second treatment is just a plain white card, so the field
     * choice is "Filled"/"White", not "Filled"/"Outlined". Each slide's
     * quote is split into prefix/stat/suffix to match the Figma text layer
     * structure (three spans: regular, bold stat, regular) instead of a
     * single free-text field with a magic placeholder token.
     *
     * @var array
     */
    public $example = [
        'style' => 'filled',
        'slides' => [
            [
                'quote_prefix' => 'Every day, cycling in Bristol takes up to ',
                'stat' => '28,000',
                'quote_suffix' => ' cars off the road.',
                'attribution' => 'BikeLife Bristol 2019',
            ],
        ],
    ];

    /**
     * @var array
     */
    public $examples = [
        'Dark' => [
            'style' => 'filled',
            'slides' => [
                [
                    'quote_prefix' => 'Every day, cycling in Bristol takes up to ',
                    'stat' => '28,000',
                    'quote_suffix' => ' cars off the road.',
                    'attribution' => 'BikeLife Bristol 2019',
                ],
                [
                    'quote_prefix' => 'Over the last year, our loan bike scheme has saved riders more than ',
                    'stat' => '£120,000',
                    'quote_suffix' => ' in fuel and parking costs.',
                    'attribution' => 'Better by Bike Annual Review',
                ],
            ],
        ],
        'Light' => [
            'style' => 'white',
            'slides' => [
                [
                    'quote_prefix' => 'Every day, cycling in Bristol takes up to ',
                    'stat' => '28,000',
                    'quote_suffix' => ' cars off the road.',
                    'attribution' => 'BikeLife Bristol 2019',
                ],
                [
                    'quote_prefix' => 'Over the last year, our loan bike scheme has saved riders more than ',
                    'stat' => '£120,000',
                    'quote_suffix' => ' in fuel and parking costs.',
                    'attribution' => 'Better by Bike Annual Review',
                ],
            ],
        ],
    ];

    public function with(): array
    {
        return [
            'style' => $this->style(),
            'slides' => $this->slides(),
        ];
    }

    public function fields(): array
    {
        $fields = Builder::make('quote_carousel');

        $fields->addSelect('style', [
            'label' => 'Style',
            'instructions' => 'Matches the Figma "Default"/"Variant2" states — Filled suits a dark section background, White suits a light one.',
            'choices' => [
                'filled' => 'Filled (dark blue)',
                'white' => 'White',
            ],
            'default_value' => 'filled',
            'required' => 1,
        ]);

        $slides = $fields->addRepeater('slides', [
            'label' => 'Slides',
            'button_label' => 'Add slide',
            'min' => 1,
            'layout' => 'block',
        ]);

        $slides
            ->addText('quote_prefix', [
                'label' => 'Quote (before stat)',
                'instructions' => 'e.g. "Every day, cycling in Bristol takes up to"',
                'required' => 1,
            ])
            ->addText('stat', [
                'label' => 'Stat (bold)',
                'instructions' => 'e.g. "28,000" — rendered in bold, inline with the quote.',
                'required' => 1,
            ])
            ->addText('quote_suffix', [
                'label' => 'Quote (after stat)',
                'instructions' => 'e.g. "cars off the road." Leave blank if the stat ends the sentence.',
                'required' => 0,
            ])
            ->addText('attribution', [
                'label' => 'Attribution / event name',
                'required' => 0,
            ]);

        $slides->endRepeater();

        return $fields->build();
    }

    public function style()
    {
        return get_field('style') ?: $this->example['style'];
    }

    public function slides()
    {
        return get_field('slides') ?: $this->example['slides'];
    }

    /**
     * @link https://developer.wordpress.org/block-editor/how-to-guides/enqueueing-assets-in-the-editor/#editor-content-scripts-and-styles
     */
    public function assets(array $block): void
    {
        //
    }
}
