<?php

namespace App\Blocks;

use App\Blocks\Concerns\ResolvesFields;
use Illuminate\Support\Facades\Vite;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class TestimonialCardCarousel extends Block
{
    use ResolvesFields;

    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Card Carousel';

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
    public $description = 'A carousel of cards, each either a testimonial (photo, name, quote) or a general content card (photo, title, text) with an optional CTA pill — using Web Awesome\'s carousel for pagination, arrows, swipe and loop.';

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
     * The block preview example data (Figma node 7:2047's "Rachel"/"Franciska"
     * cards, plus a third to demonstrate the carousel with more than two
     * slides on the pattern library page).
     *
     * @var array
     */
    public $example = [
        'cards' => [
            [
                'card_type' => 'testimonial',
                'photo' => ['url' => 'https://betterbybike.info/wp-content/uploads/placeholder.jpg', 'alt' => ''],
                'name' => 'Rachel',
                'info' => 'Cycling through Portishead.',
                'quote' => 'I ride to avoid the cost and stress of driving and parking plus getting some extra fitness in.',
                'link' => ['title' => 'Read more', 'url' => '#', 'target' => ''],
            ],
            [
                'photo' => ['url' => 'https://betterbybike.info/wp-content/uploads/placeholder.jpg', 'alt' => ''],
                'name' => 'Franciska',
                'info' => 'Cycling through The Arches, Bristol.',
                'quote' => "It keeps me healthy. I don't feel tired like I used to. I used to get very bad headaches but I don't get them anymore since I started cycling.",
                'link' => ['title' => 'Read more', 'url' => '#', 'target' => ''],
            ],
            [
                'card_type' => 'testimonial',
                'photo' => ['url' => 'https://betterbybike.info/wp-content/uploads/placeholder.jpg', 'alt' => ''],
                'name' => 'Tom',
                'info' => 'Cycling to work in Bath.',
                'quote' => 'Cycling to work means I never have to worry about parking, and I arrive feeling ready for the day.',
                'link' => ['title' => 'Read more', 'url' => '#', 'target' => ''],
            ],
        ],
    ];

    /**
     * Card type variants to render stacked on the pattern library page (see
     * App\View\Composers\PatternLibrary::render()) — each entry is merged
     * onto $example above, so only needs to override what differs.
     *
     * @var array
     */
    public $examples = [
        'Testimonial' => [],
        'Content' => [
            'cards' => [
                [
                    'card_type' => 'content',
                    'photo' => ['url' => 'https://betterbybike.info/wp-content/uploads/placeholder.jpg', 'alt' => ''],
                    'name' => 'Loan bikes',
                    'info' => 'Try before you buy.',
                    'body' => 'Borrow a bike for up to a month to find out whether cycling is right for you.',
                    'link' => ['title' => 'Find out more', 'url' => '#', 'target' => ''],
                ],
                [
                    'card_type' => 'content',
                    'photo' => ['url' => 'https://betterbybike.info/wp-content/uploads/placeholder.jpg', 'alt' => ''],
                    'name' => 'Cycle training',
                    'info' => 'For all ages and abilities.',
                    'body' => 'Friendly one-to-one and group sessions to build your skills and confidence on the road.',
                    'link' => ['title' => 'Find out more', 'url' => '#', 'target' => ''],
                ],
                [
                    'card_type' => 'content',
                    'photo' => ['url' => 'https://betterbybike.info/wp-content/uploads/placeholder.jpg', 'alt' => ''],
                    'name' => 'Bristol Cycle Centre sessions',
                    'info' => 'Drop in or book ahead.',
                    'body' => 'Maintenance workshops, led rides and advice sessions run throughout the year.',
                    'link' => ['title' => 'Find out more', 'url' => '#', 'target' => ''],
                ],
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
            'cards' => array_map(fn($card) => array_merge($card, ['photo' => $placeholder]), $this->example['cards']),
        ];
    }

    public function with(): array
    {
        return [
            'cards' => $this->cards(),
        ];
    }

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

        $isType = fn(string $type) => [
            [
                [
                    'field' => 'card_type',
                    'operator' => '==',
                    'value' => $type,
                ],
            ],
        ];

        $cards
            ->addSelect('card_type', [
                'label' => 'Card type',
                'instructions' => 'Testimonial shows a pull-quote. Content shows a short text description, for e.g. a case study, loan bikes, cycle training or sessions.',
                'choices' => [
                    'testimonial' => 'Testimonial',
                    'content' => 'Content',
                ],
                'default_value' => 'testimonial',
                'ui' => true,
            ])
            ->addImage('photo', [
                'label' => 'Photo',
                'instructions' => 'Photo of the person giving the testimonial, or an image for the content card.',
                'return_format' => 'array',
                'preview_size' => 'medium',
                'required' => 1,
            ])
            ->addText('name', [
                'label' => 'Name / title',
                'required' => 1,
            ])
            ->addText('info', [
                'label' => 'Additional info',
                'instructions' => 'Shown under the name or title, e.g. "Cycling through Portishead". Optional.',
            ])
            ->addTextarea('quote', [
                'label' => 'Quote',
                'required' => 1,
                'rows' => 4,
                'new_lines' => 'wpautop', // wraps each line in <p> — matches .c-quote__body's p + p spacing
                'conditional_logic' => $isType('testimonial'),
            ])
            ->addTextarea('body', [
                'label' => 'Text',
                'required' => 1,
                'rows' => 4,
                'new_lines' => 'wpautop',
                'conditional_logic' => $isType('content'),
            ])
            ->addLink('link', [
                'label' => 'CTA link',
                'instructions' => 'Text + URL for the CTA pill. Defaults to "Read more" if no text is set.',
                'required' => false,
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

        $cards = $this->resolveField('cards', []);

        return array_map(function ($card) use ($placeholder) {
            $photo = is_array($card['photo'] ?? null) ? $card['photo'] : [];
            $name = $card['name'] ?? '';

            if (empty($photo['url']) || $photo['url'] === 'https://betterbybike.info/wp-content/uploads/placeholder.jpg') {
                $photo = ['url' => $placeholder, 'alt' => $photo['alt'] ?? ''];
            }

            // Rows saved before `card_type` existed are all testimonials.
            $type = ($card['card_type'] ?? '') === 'content' ? 'content' : 'testimonial';

            // Real ACF data is already wpautop-formatted (the sub-fields' own
            // `new_lines` setting); the plain-text $example fallback isn't,
            // so it needs the same pass manually — checked for, not assumed,
            // to avoid double-wrapping already-formatted content.
            $format = fn($text) => $text !== '' && stripos($text, '<p') === false ? wpautop($text) : $text;

            return [
                'type' => $type,
                'photo' => [
                    'url' => $photo['url'],
                    'alt' => $photo['alt'] ?: $name,
                ],
                'name' => $name,
                'info' => $card['info'] ?? '',
                'quote' => $format($card['quote'] ?? ''),
                'body' => $format($card['body'] ?? ''),
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
