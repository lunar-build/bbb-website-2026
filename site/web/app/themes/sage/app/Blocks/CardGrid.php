<?php

namespace App\Blocks;

use App\Blocks\Concerns\ResolvesFields;
use App\Blocks\Concerns\HasBusinessCardFields;
use App\Fields\Copy;
use App\Fields\Heading;
use Illuminate\Support\Facades\Vite;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class CardGrid extends Block
{
    use HasBusinessCardFields;
    use ResolvesFields;

    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Card Grid';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'card-grid';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A section heading with a grid of cards (FeatureCard\'s Link/Bikeability/Event/News styles, a plain Image card, or a Business card listing), with an optional decorative image beside the grid on large screens.';

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
    public $icon = 'layout';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'card',
        'grid',
        'cards',
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
        'heading_text' => 'Get confidence — visit a cycling centre',
        'heading_level' => 'h2',
        'heading_style' => 'match',
        'intro_text' => 'Discover charities and organisations with a range of specially adapted bikes and training sessions aimed at a wide range of people to help you get back on a bike!',
        'intro_style' => 'body',
        'card_style' => 'link',
        'cards' => [
            [
                'image' => ['url' => 'https://betterbybike.info/wp-content/uploads/placeholder.jpg', 'alt' => ''],
                'heading_text' => 'Bristol Cycling Centre',
                'heading_level' => 'h3',
                'heading_style' => 'match',
                'body_text' => '',
                'date' => null,
                'link' => ['title' => 'Bristol Cycling Centre', 'url' => '#', 'target' => ''],
            ],
            [
                'image' => ['url' => 'https://betterbybike.info/wp-content/uploads/placeholder.jpg', 'alt' => ''],
                'heading_text' => 'All Cycle Bath & West',
                'heading_level' => 'h3',
                'heading_style' => 'match',
                'body_text' => '',
                'date' => null,
                'link' => ['title' => 'All Cycle Bath & West', 'url' => '#', 'target' => ''],
            ],
            [
                'image' => ['url' => 'https://betterbybike.info/wp-content/uploads/placeholder.jpg', 'alt' => ''],
                'heading_text' => 'Strawberry Line Cycle Project',
                'heading_level' => 'h3',
                'heading_style' => 'match',
                'body_text' => '',
                'date' => null,
                'link' => ['title' => 'Strawberry Line Cycle Project', 'url' => '#', 'target' => ''],
            ],
            [
                'image' => ['url' => 'https://betterbybike.info/wp-content/uploads/placeholder.jpg', 'alt' => ''],
                'heading_text' => 'Warmley Wheelers — South Glos',
                'heading_level' => 'h3',
                'heading_style' => 'match',
                'body_text' => '',
                'date' => null,
                'link' => ['title' => 'Warmley Wheelers — South Glos', 'url' => '#', 'target' => ''],
            ],
        ],
    ];

    /**
     * Card style variants to render stacked on the pattern-library page
     * (see App\View\Composers\PatternLibrary::render()) — each entry is
     * merged onto $example above, so only needs to override what differs.
     *
     * @var array
     */
    public $examples = [
        'Link' => [],
        'Event' => [
            'card_style' => 'event',
            'cards' => [
                ['date' => '2nd July 2026'],
                ['date' => '9th July 2026'],
            ],
        ],
        'Image card' => [
            'card_style' => 'image_card',
        ],
        'Link (with side image)' => [
            'show_side_image' => true,
        ],
        'Business card' => [
            'card_style' => 'business',
            'business_cards' => [
                [
                    'name' => 'Weston Bicycle Works',
                    'contact_info' => [
                        ['icon' => 'location-dot', 'text' => '143 Locking Road, Weston-Super-Mare, BS23 3ER'],
                        ['icon' => 'phone', 'text' => '01934 629989'],
                    ],
                    'services' => [
                        ['icon' => 'recycle', 'text' => 'Secondhand bikes'],
                        ['icon' => 'wrench', 'text' => 'Bike servicing'],
                        ['icon' => 'hand-holding-heart', 'text' => 'Donate a bike'],
                    ],
                    'description' => 'A community enterprise selling quality refurbished bikes and welcome bike donations.',
                    'link' => ['title' => 'Learn more', 'url' => '#', 'target' => '_blank'],
                ],
                [
                    'name' => 'Bristol Bike Project',
                    'contact_info' => [
                        ['icon' => 'location-dot', 'text' => '18 Wade Street, St Jude\'s, Bristol, BS2 9UD'],
                        ['icon' => 'phone', 'text' => '0117 123 4567'],
                    ],
                    'services' => [
                        ['icon' => 'bicycle', 'text' => 'New bikes'],
                        ['icon' => 'wrench', 'text' => 'Bike servicing'],
                    ],
                    'description' => 'A social enterprise bike shop and workshop, running earn-a-bike schemes.',
                    'link' => ['title' => 'Learn more', 'url' => '#', 'target' => '_blank'],
                ],
            ],
        ],
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'heading' => $this->heading(),
            'intro' => $this->intro(),
            'cardStyle' => $this->cardStyle(),
            'ctaStyle' => $this->ctaStyle(),
            'cards' => $this->cards(),
            'businessCards' => $this->businessCards(),
            'sideImage' => $this->sideImage(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('card_grid');

        $fields->addTab('Content');

        $fields->addPartial(Heading::class, [
            'name' => 'heading',
            'label' => 'Heading',
            'default_level' => 'h2',
        ]);

        $fields->addPartial(Copy::class, [
            'name' => 'intro',
            'label' => 'Intro text',
            'default_style' => 'body',
        ]);

        $fields
            ->addSelect('card_style', [
                'label' => 'Card style',
                'instructions' => 'Applies to every card in this grid — same visual treatments as the Feature Card block. Changing it swaps which cards tab is shown; cards entered under another style are kept but not displayed.',
                'choices' => [
                    'link' => 'Link with picture (arrow-only CTA, no date)',
                    'bikeability' => 'Bikeability (button CTA, no date)',
                    'event' => 'Event card (button CTA, date shown)',
                    'news' => 'News card (button CTA, date shown, no body copy)',
                    'image_card' => 'Image card (image + linked caption bar only)',
                    'business' => 'Business card (name, contact info, services, description — no image)',
                ],
                'default_value' => 'link',
                'ui' => true,
            ])
            ->addTrueFalse('show_side_image', [
                'label' => 'Show side image',
                'instructions' => 'Decorative illustration standing at the bottom-right of the grid, large screens only — hidden on mobile/tablet. Image is fixed for now, not editable.',
                'default_value' => 0,
                'ui' => true,
            ]);

        // One cards tab per set of card styles that share the same fields —
        // an ACF sub-field can't be shown/hidden by a field outside its own
        // repeater, so the card style swaps whole tabs instead.
        $this->addCardsTab($fields, 'Cards', 'cards', ['link', 'bikeability'], ['body' => true, 'date' => false]);
        $this->addCardsTab($fields, 'Event cards', 'event_cards', ['event'], ['body' => true, 'date' => true]);
        $this->addCardsTab($fields, 'News cards', 'news_cards', ['news'], ['body' => false, 'date' => true]);

        $fields->addTab('Image cards', $this->whenCardStyle(['image_card']));

        $imageCards = $fields->addRepeater('image_cards', [
            'label' => 'Cards',
            'button_label' => 'Add card',
            'min' => 0,
            'layout' => 'block',
        ]);

        $imageCards
            ->addImage('image', [
                'label' => 'Image',
                'return_format' => 'array',
                'preview_size' => 'medium',
                'required' => 1,
            ])
            ->addLink('link', [
                'label' => 'Link',
                'instructions' => "Link's title is shown as the caption under the image.",
                'required' => true,
            ]);

        $imageCards->endRepeater();

        $fields->addTab('Business cards', $this->whenCardStyle(['business']));

        $businessCards = $fields->addRepeater('business_cards', [
            'label' => 'Business cards',
            'button_label' => 'Add card',
            'min' => 0,
            'layout' => 'block',
        ]);

        $this->addBusinessCardFields($businessCards);

        $businessCards->endRepeater();

        return $fields->build();
    }

    /**
     * ACF conditional_logic showing a field/tab only for the given card styles.
     *
     * @param  array  $styles
     * @return array
     */
    protected function whenCardStyle(array $styles): array
    {
        return [
            'conditional_logic' => array_map(
                fn ($style) => [['field' => 'card_style', 'operator' => '==', 'value' => $style]],
                $styles,
            ),
        ];
    }

    /**
     * Add a tab + repeater of image/title/(body)/(date)/link cards, shown
     * only for the given card styles.
     *
     * @param  array  $show  'body' and 'date' — whether those sub-fields apply.
     */
    protected function addCardsTab($fields, string $tab, string $name, array $styles, array $show): void
    {
        $fields->addTab($tab, $this->whenCardStyle($styles));

        $cards = $fields->addRepeater($name, [
            'label' => 'Cards',
            'button_label' => 'Add card',
            'min' => 0,
            'layout' => 'block',
        ]);

        $cards->addImage('image', [
            'label' => 'Image',
            'return_format' => 'array',
            'preview_size' => 'medium',
            'required' => 1,
        ]);

        $cards->addPartial(Heading::class, [
            'name' => 'heading',
            'label' => 'Title',
            'default_level' => 'h3',
            'required' => true,
        ]);

        if ($show['body']) {
            $cards->addPartial(Copy::class, [
                'name' => 'body',
                'label' => 'Body text',
                'default_style' => 'body',
            ]);
        }

        if ($show['date']) {
            $cards->addDatePicker('date', [
                'label' => 'Date',
                'display_format' => 'jS F Y',
                'return_format' => 'jS F Y',
            ]);
        }

        $cards->addLink('link', [
            'label' => 'Link',
            'required' => true,
        ]);

        $cards->endRepeater();
    }

    /**
     * Retrieve the section heading text/level/style.
     *
     * @return array
     */
    public function heading()
    {
        return [
            'text' => $this->resolveField('heading_text', ''),
            'level' => $this->resolveField('heading_level', 'h2'),
            'style' => $this->resolveField('heading_style', 'match'),
        ];
    }

    /**
     * Retrieve the intro text/style. Empty text is valid — the intro is optional.
     *
     * @return array
     */
    public function intro()
    {
        return [
            'text' => $this->resolveField('intro_text', ''),
            'style' => $this->resolveField('intro_style', 'body'),
        ];
    }

    /**
     * Retrieve the card style applied to the whole grid.
     *
     * @return string
     */
    public function cardStyle()
    {
        return $this->resolveField('card_style', 'link');
    }

    /**
     * Retrieve the CTA style implied by the grid's card style — mirrors
     * FeatureCard::ctaStyle()'s "link" → icon-only, everything else → button.
     *
     * @return string
     */
    public function ctaStyle()
    {
        return $this->cardStyle() === 'link' ? 'icon' : 'button';
    }

    /**
     * Retrieve the optional decorative side image (large screens only). The
     * image itself is fixed/hardcoded for now — matches the Figma asset —
     * not an editable field; `show_side_image` only toggles it on/off.
     *
     * @return string|null
     */
    public function sideImage()
    {
        $show = $this->resolveBoolField('show_side_image');

        return $show ? Vite::asset('resources/images/illustrations/instructor.png') : null;
    }

    /**
     * Retrieve the cards, normalized to a safe shape and with the date
     * cleared unless the grid's card style actually shows one.
     *
     * @return array
     */
    public function cards()
    {
        $placeholder = Vite::asset('resources/images/placeholder/pattern-placeholder.svg');
        $showDate = in_array($this->cardStyle(), ['event', 'news'], true);

        $field = match ($this->cardStyle()) {
            'event' => 'event_cards',
            'news' => 'news_cards',
            'image_card' => 'image_cards',
            default => 'cards',
        };

        $cards = get_field($field) ?: $this->exampleField($field, $this->exampleField('cards', []));

        return array_map(function ($card) use ($placeholder, $showDate) {
            $image = is_array($card['image'] ?? null) ? $card['image'] : [];

            if (empty($image['url']) || $image['url'] === 'https://betterbybike.info/wp-content/uploads/placeholder.jpg') {
                $image = ['url' => $placeholder, 'alt' => $image['alt'] ?? ''];
            }

            return [
                'image' => $image,
                'heading' => [
                    'text' => $card['heading_text'] ?? '',
                    'level' => $card['heading_level'] ?? 'h3',
                    'style' => $card['heading_style'] ?? 'match',
                ],
                'body' => [
                    'text' => $card['body_text'] ?? '',
                    'style' => $card['body_style'] ?? 'body',
                ],
                'date' => $showDate ? ($card['date'] ?? null) : null,
                'link' => normalize_link($card['link'] ?? null),
            ];
        }, $cards);
    }

    /**
     * Retrieve the Business card style's cards, each augmented with
     * contact-info tap/click-through hrefs (see HasBusinessCardFields).
     *
     * @return array
     */
    public function businessCards()
    {
        $cards = $this->resolveField('business_cards', []);

        return array_map(function ($card) {
            $card['contact_info'] = $this->withBusinessCardContactHrefs($card['contact_info'] ?? []);

            return $card;
        }, $cards);
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
