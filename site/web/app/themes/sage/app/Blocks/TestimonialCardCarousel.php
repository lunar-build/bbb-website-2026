<?php

namespace App\Blocks;

use Illuminate\Support\Facades\Vite;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class TestimonialCardCarousel extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Testimonial Card Carousel';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'testimonial-card-carousel';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A carousel of testimonial cards — person photo, name, quote, and a "Read more" CTA pill — using Web Awesome\'s carousel for pagination, arrows, swipe and loop.';

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
    public $icon = 'images-alt2';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'testimonial',
        'carousel',
        'quote',
        'card',
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
     * The block preview example data (Figma node 7:2047's "Rachel"/"Franciska"
     * cards, plus a third to demonstrate the carousel with more than two
     * slides on the pattern library page).
     *
     * @var array
     */
    public $example = [
        'cards' => [
            [
                'photo' => ['url' => 'https://betterbybike.info/wp-content/uploads/placeholder.jpg', 'alt' => ''],
                'name' => 'Rachel',
                'quote' => 'I ride to avoid the cost and stress of driving and parking plus getting some extra fitness in.',
                'link' => ['title' => 'Read more', 'url' => '#', 'target' => ''],
            ],
            [
                'photo' => ['url' => 'https://betterbybike.info/wp-content/uploads/placeholder.jpg', 'alt' => ''],
                'name' => 'Franciska',
                'quote' => "It keeps me healthy. I don't feel tired like I used to. I used to get very bad headaches but I don't get them anymore since I started cycling.",
                'link' => ['title' => 'Read more', 'url' => '#', 'target' => ''],
            ],
            [
                'photo' => ['url' => 'https://betterbybike.info/wp-content/uploads/placeholder.jpg', 'alt' => ''],
                'name' => 'Tom',
                'quote' => 'Cycling to work means I never have to worry about parking, and I arrive feeling ready for the day.',
                'link' => ['title' => 'Read more', 'url' => '#', 'target' => ''],
            ],
        ],
    ];

    /**
     * Fallback example data requiring a non-constant expression (Vite::asset).
     *
     * @return array
     */
    public function example(): array
    {
        $placeholder = ['url' => Vite::asset('resources/images/placeholder/pattern-placeholder.svg'), 'alt' => ''];

        return [
            'cards' => array_map(fn ($card) => array_merge($card, ['photo' => $placeholder]), $this->example['cards']),
        ];
    }

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'cards' => $this->cards(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('testimonial_card_carousel');

        $cards = $fields->addRepeater('cards', [
            'label' => 'Cards',
            'instructions' => 'One card per carousel slide.',
            'button_label' => 'Add card',
            'min' => 1,
            'layout' => 'block',
        ]);

        $cards
            ->addImage('photo', [
                'label' => 'Photo',
                'instructions' => 'Photo of the person giving the testimonial.',
                'return_format' => 'array',
                'preview_size' => 'medium',
                'required' => 1,
            ])
            ->addText('name', [
                'label' => 'Name',
                'required' => 1,
            ])
            ->addTextarea('quote', [
                'label' => 'Quote',
                'required' => 1,
                'rows' => 4,
                'new_lines' => 'wpautop', // wraps each line in <p> — matches .c-quote__body's p + p spacing
            ])
            ->addLink('link', [
                'label' => 'Read more link',
                'instructions' => 'Text + URL for the "Read more" CTA pill.',
                'required' => true,
            ]);

        $cards->endRepeater();

        return $fields->build();
    }

    /**
     * Retrieve the cards, normalized to a safe shape — image fallback to the
     * pattern-placeholder asset, alt text fallback to the person's name (no
     * dedicated alt field on this design, matching CycleRouteCard/ImageCard's
     * convention of trusting the media library alt with a sensible default),
     * and the link normalized via the shared `normalize_link()` helper.
     *
     * @return array
     */
    public function cards()
    {
        $placeholder = Vite::asset('resources/images/placeholder/pattern-placeholder.svg');

        $cards = get_field('cards') ?: ($this->example['cards'] ?? []);

        return array_map(function ($card) use ($placeholder) {
            $photo = is_array($card['photo'] ?? null) ? $card['photo'] : [];
            $name = $card['name'] ?? '';

            if (empty($photo['url']) || $photo['url'] === 'https://betterbybike.info/wp-content/uploads/placeholder.jpg') {
                $photo = ['url' => $placeholder, 'alt' => $photo['alt'] ?? ''];
            }

            $quote = $card['quote'] ?? '';

            // Real ACF data is already wpautop-formatted (the `quote` sub-field's
            // own `new_lines` setting); the plain-text $example fallback isn't,
            // so it needs the same pass manually — checked for, not assumed,
            // to avoid double-wrapping already-formatted content.
            if ($quote !== '' && stripos($quote, '<p') === false) {
                $quote = wpautop($quote);
            }

            return [
                'photo' => [
                    'url' => $photo['url'],
                    'alt' => $photo['alt'] ?: $name,
                ],
                'name' => $name,
                'quote' => $quote,
                'link' => normalize_link($card['link'] ?? null),
            ];
        }, $cards);
    }

    /**
     * @link https://developer.wordpress.org/block-editor/how-to-guides/enqueueing-assets-in-the-editor/#editor-content-scripts-and-styles
     */
    public function assets(array $block): void
    {
        //
    }
}
