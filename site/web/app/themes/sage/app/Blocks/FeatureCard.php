<?php

namespace App\Blocks;

use Illuminate\Support\Facades\Vite;
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
    public $description = 'A flexible image card for events, news listings, and partner logos/links.';

    /**
     * The block category.
     *
     * @var string
     */
    public $category = 'cards';

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
     * The block preview example data.
     *
     * @var array
     */
    public $example = [
        'card_style' => 'link',
        'cta_style' => 'icon',
        'link' => [
            'title' => 'Get involved',
            'url' => 'https://betterbybike.info/get-involved/',
            'target' => '',
        ],
    ];

    /**
     * Style variants to render stacked on the pattern-library page (see
     * App\View\Composers\PatternLibrary::render()) — each entry is merged
     * onto $example above, so only needs to override what differs. Matches
     * the four "Image and content card" instances in Figma (node 9-3214):
     * Link with picture, Bikeability, Event card, News card.
     *
     * @var array
     */
    public $examples = [
        'Link' => [],
        'Bikeability' => ['card_style' => 'bikeability', 'cta_style' => 'button'],
        'Event' => ['card_style' => 'event', 'cta_style' => 'button', 'date' => '2nd July 2026'],
        'News' => ['card_style' => 'news', 'cta_style' => 'button', 'date' => '12th June 2026'],
    ];

    /**
     * Fallback example data requiring a non-constant expression (Vite::asset).
     *
     * @return array
     */
    public function example(): array
    {
        return [
            'image' => ['url' => Vite::asset('resources/images/placeholder/pattern-placeholder.svg')],
        ];
    }

    /**
     * The block template.
     *
     * @var array
     */
    public $template = [
        'core/heading' => ['placeholder' => 'Title', 'level' => 4],
        'core/paragraph' => ['placeholder' => 'Body text (optional — delete for cards without body copy)…'],
    ];

    /**
     * Fixture markup standing in for this block's InnerBlocks content on
     * the pattern library page (see App\View\Composers\PatternLibrary).
     *
     * @var string
     */
    public $exampleContent = '<h3>Loan a bike</h3><p>Example body copy for the card — replace with real content.</p>';

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'cardStyle' => $this->cardStyle(),
            'image' => $this->image(),
            'date' => $this->date(),
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
            ->addTab('Style')
                ->addSelect('card_style', [
                    'label' => 'Card style',
                    'instructions' => 'Visual treatment matching the Figma "Image and content card" variant this content represents.',
                    'choices' => [
                        'link' => 'Link with picture (arrow-only CTA, no date)',
                        'bikeability' => 'Bikeability (button CTA, no date — e.g. partner logos)',
                        'event' => 'Event card (button CTA, date shown)',
                        'news' => 'News card (button CTA, date shown, no body copy)',
                    ],
                    'default_value' => 'link',
                    'ui' => true,
                ])
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
                    'instructions' => 'Shown above the heading on Event and News styles.',
                    'display_format' => 'jS F Y',
                    'return_format' => 'jS F Y',
                    'conditional_logic' => [
                        [
                            [
                                'field' => 'card_style',
                                'operator' => '==',
                                'value' => 'event',
                            ],
                        ],
                        [
                            [
                                'field' => 'card_style',
                                'operator' => '==',
                                'value' => 'news',
                            ],
                        ],
                    ],
                ])
            ->addTab('Call to Action')
                ->addLink('link', [
                    'label' => 'Link',
                    'instructions' => 'URL the card links to. Leave empty for no link at all.',
                ])
                ->addSelect('cta_style', [
                    'label' => 'CTA style',
                    'instructions' => 'How the link is presented. Ignored if the Link field above is empty. Figma pairs "Link with picture" with an arrow-only CTA, and Bikeability/Event/News with a button.',
                    'choices' => [
                        'button' => 'Button (filled pill, visible label)',
                        'icon' => 'Icon only (arrow, no label)',
                        'none' => 'No visible CTA — card is still fully clickable via Link',
                    ],
                    'default_value' => 'button',
                ]);

        return $fields->build();
    }

    /**
     * Retrieve the card style.
     *
     * @return string
     */
    public function cardStyle()
    {
        return get_field('card_style') ?: $this->example['card_style'];
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
     * Retrieve the date.
     *
     * @return string|null
     */
    public function date()
    {
        return get_field('date') ?: ($this->example['date'] ?? null);
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
        return get_field('cta_style') ?: ($this->example['cta_style'] ?? 'button');
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
