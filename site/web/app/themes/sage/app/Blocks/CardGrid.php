<?php

namespace App\Blocks;

use App\Fields\Copy;
use App\Fields\Heading;
use Illuminate\Support\Facades\Vite;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class CardGrid extends Block
{
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
    public $description = 'A section heading with a grid of cards (FeatureCard\'s Link/Bikeability/Event/News styles, or a plain Image card), with an optional decorative image beside the grid on large screens.';

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
            'sideImage' => $this->sideImage(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('card_grid');

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
                'instructions' => 'Applies to every card in this grid — same visual treatments as the Feature Card block.',
                'choices' => [
                    'link' => 'Link with picture (arrow-only CTA, no date)',
                    'bikeability' => 'Bikeability (button CTA, no date)',
                    'event' => 'Event card (button CTA, date shown)',
                    'news' => 'News card (button CTA, date shown, no body copy)',
                    'image_card' => 'Image card (image + linked caption bar only, no heading/body/date)',
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

        $cards = $fields->addRepeater('cards', [
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

        $cards->addPartial(Copy::class, [
            'name' => 'body',
            'label' => 'Body text',
            'default_style' => 'body',
        ]);

        $cards
            ->addDatePicker('date', [
                'label' => 'Date',
                'instructions' => 'Only shown when the grid\'s Card style is Event or News.',
                'display_format' => 'jS F Y',
                'return_format' => 'jS F Y',
            ])
            ->addLink('link', [
                'label' => 'Link',
                'required' => true,
            ]);

        $cards->endRepeater();

        return $fields->build();
    }

    /**
     * Retrieve the section heading text/level/style.
     *
     * @return array
     */
    public function heading()
    {
        return [
            'text' => get_field('heading_text') ?: ($this->example['heading_text'] ?? ''),
            'level' => get_field('heading_level') ?: ($this->example['heading_level'] ?? 'h2'),
            'style' => get_field('heading_style') ?: ($this->example['heading_style'] ?? 'match'),
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
            'text' => get_field('intro_text') ?: ($this->example['intro_text'] ?? ''),
            'style' => get_field('intro_style') ?: ($this->example['intro_style'] ?? 'body'),
        ];
    }

    /**
     * Retrieve the card style applied to the whole grid.
     *
     * @return string
     */
    public function cardStyle()
    {
        return get_field('card_style') ?: ($this->example['card_style'] ?? 'link');
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
        $show = get_field('show_side_image');
        $show = $show !== null ? (bool) $show : (bool) ($this->example['show_side_image'] ?? false);

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

        $cards = get_field('cards') ?: ($this->example['cards'] ?? []);

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
     * Assets enqueued with 'enqueue_block_assets' when rendering the block.
     *
     * @link https://developer.wordpress.org/block-editor/how-to-guides/enqueueing-assets-in-the-editor/#editor-content-scripts-and-styles
     */
    public function assets(array $block): void
    {
        //
    }
}
