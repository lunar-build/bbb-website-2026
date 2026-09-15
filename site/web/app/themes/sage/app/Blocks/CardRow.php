<?php

namespace App\Blocks;

use Illuminate\Support\Facades\Vite;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class CardRow extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Card Row';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'card-row';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A section heading with a row of cards, in News/Route/Link layouts.';

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
    public $icon = 'grid-view';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'card',
        'row',
        'news',
        'route',
        'link',
        'grid',
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
        'type' => 'news',
        'heading_highlight' => 'Latest',
        'heading' => 'news',
        'news_cards' => [
            [
                'image' => ['url' => 'https://betterbybike.info/wp-content/uploads/placeholder.jpg', 'alt' => ''],
                'tag' => 'Events',
                'title' => 'Shredfest – New Mountain Bike Festival to bring bikes, families and community spirit to Ashton Court this July',
                'link' => ['title' => 'Read more', 'url' => '#', 'target' => ''],
            ],
            [
                'image' => ['url' => 'https://betterbybike.info/wp-content/uploads/placeholder.jpg', 'alt' => ''],
                'tag' => 'Improvements',
                'title' => 'Broadmead lighting, sails structure and food kiosks to be removed ahead of junction transformation',
                'link' => ['title' => 'Read more', 'url' => '#', 'target' => ''],
            ],
            [
                'image' => ['url' => 'https://betterbybike.info/wp-content/uploads/placeholder.jpg', 'alt' => ''],
                'tag' => 'Accessibility',
                'title' => 'Sparke Evans Park Bridge reopens with new accessible ramp',
                'link' => ['title' => 'Read more', 'url' => '#', 'target' => ''],
            ],
        ],
        'browse_all_link' => ['title' => 'Browse all news', 'url' => '#', 'target' => ''],
    ];

    /**
     * Type variants to render stacked on the pattern-library page (see
     * App\View\Composers\PatternLibrary::render()) — each entry is merged
     * onto $example above, so only needs to override what differs.
     *
     * @var array
     */
    public $examples = [
        'News' => [],
        'Route' => [
            'type' => 'route',
            'heading_highlight' => 'Discover',
            'heading' => 'our favourite local rides',
            'route_cards' => [
                [
                    'image' => ['url' => 'https://betterbybike.info/wp-content/uploads/placeholder.jpg', 'alt' => ''],
                    'route_name' => 'Bristol to Bath Railway Path',
                    'time_needed' => '2-3 hours',
                    'distance' => '13 miles',
                    'difficulty' => 'easy',
                    'link' => ['title' => 'View route', 'url' => '#', 'target' => ''],
                ],
                [
                    'image' => ['url' => 'https://betterbybike.info/wp-content/uploads/placeholder.jpg', 'alt' => ''],
                    'route_name' => 'Brean Down Way',
                    'time_needed' => '1 hour',
                    'distance' => '5 miles',
                    'difficulty' => 'moderate',
                    'link' => ['title' => 'View route', 'url' => '#', 'target' => ''],
                ],
            ],
            'area_links' => [
                ['link' => ['title' => 'Bristol Cycle Routes', 'url' => '#', 'target' => '']],
                ['link' => ['title' => 'Bath & NE Somerset Cycle Routes', 'url' => '#', 'target' => '']],
            ],
            'browse_all_link' => ['title' => 'Plan your own route', 'url' => '#', 'target' => ''],
        ],
        'Link' => [
            'type' => 'link',
            'heading_highlight' => 'Explore',
            'heading' => 'ways to get a bike',
            'intro' => 'There are so many ways to get a bike, discover one that suits you best.',
            'link_cards' => [
                [
                    'image' => ['url' => 'https://betterbybike.info/wp-content/uploads/placeholder.jpg', 'alt' => ''],
                    'title' => 'Bike shops',
                    'body' => 'These shops offer a range of bike types across a wide price range. Many offer a tax discount via the Cycle To Work scheme.',
                    'link' => ['title' => 'Bike shops', 'url' => '#', 'target' => ''],
                ],
                [
                    'image' => ['url' => 'https://betterbybike.info/wp-content/uploads/placeholder.jpg', 'alt' => ''],
                    'title' => 'Borrow a bike scheme',
                    'body' => 'The Borrow A Bike scheme is a great way to boost your confidence and discover if cycling is for you before you take the plunge.',
                    'link' => ['title' => 'Borrow a bike', 'url' => '#', 'target' => ''],
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
            'type' => $this->type(),
            'headingHighlight' => $this->headingHighlight(),
            'heading' => $this->heading(),
            'intro' => $this->intro(),
            'newsCards' => $this->newsCards(),
            'routeCards' => $this->routeCards(),
            'linkCards' => $this->linkCards(),
            'areaLinks' => $this->areaLinks(),
            'browseAllLink' => $this->browseAllLink(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('card_row');

        $fields
            ->addTab('Content')
                ->addSelect('type', [
                    'label' => 'Type',
                    'choices' => [
                        'news' => 'News',
                        'route' => 'Route',
                        'link' => 'Link',
                    ],
                    'default_value' => 'news',
                    'ui' => true,
                ])
                ->addText('heading_highlight', [
                    'label' => 'Heading (highlighted phrase)',
                    'instructions' => 'Short lead phrase, shown in a lighter blue.',
                    'required' => 1,
                ])
                ->addText('heading', [
                    'label' => 'Heading (rest)',
                    'required' => 1,
                ])
                ->addTextarea('intro', [
                    'label' => 'Intro text',
                    'instructions' => 'Only shown on the Link type.',
                    'rows' => 2,
                    'conditional_logic' => [
                        [
                            [
                                'field' => 'type',
                                'operator' => '==',
                                'value' => 'link',
                            ],
                        ],
                    ],
                ])
                ->addLink('browse_all_link', [
                    'label' => 'Browse all link',
                    'instructions' => 'Bottom CTA. Not shown on the Link type.',
                    'conditional_logic' => [
                        [
                            [
                                'field' => 'type',
                                'operator' => '!=',
                                'value' => 'link',
                            ],
                        ],
                    ],
                ])
            ->addTab('News cards', [
                'conditional_logic' => [
                    [
                        [
                            'field' => 'type',
                            'operator' => '==',
                            'value' => 'news',
                        ],
                    ],
                ],
            ])
                ->addRepeater('news_cards', [
                    'label' => 'Cards',
                    'button_label' => 'Add card',
                    'min' => 0,
                    'layout' => 'block',
                ])
                    ->addImage('image', [
                        'label' => 'Image',
                        'return_format' => 'array',
                        'preview_size' => 'thumbnail',
                        'required' => 1,
                    ])
                    ->addText('tag', [
                        'label' => 'Category tag',
                        'required' => 1,
                    ])
                    ->addText('title', [
                        'label' => 'Title',
                        'required' => 1,
                    ])
                    ->addLink('link', [
                        'label' => 'Link',
                        'required' => true,
                    ])
                ->endRepeater()
            ->addTab('Route cards', [
                'conditional_logic' => [
                    [
                        [
                            'field' => 'type',
                            'operator' => '==',
                            'value' => 'route',
                        ],
                    ],
                ],
            ])
                ->addRepeater('route_cards', [
                    'label' => 'Cards',
                    'button_label' => 'Add card',
                    'min' => 0,
                    'layout' => 'block',
                ])
                    ->addImage('image', [
                        'label' => 'Image',
                        'return_format' => 'array',
                        'preview_size' => 'thumbnail',
                        'required' => 1,
                    ])
                    ->addText('route_name', [
                        'label' => 'Route name',
                        'required' => 1,
                    ])
                    ->addText('time_needed', [
                        'label' => 'Time needed',
                        'required' => 1,
                    ])
                    ->addText('distance', [
                        'label' => 'Distance',
                        'required' => 1,
                    ])
                    ->addSelect('difficulty', [
                        'label' => 'Difficulty',
                        'choices' => [
                            'easy' => 'Easy',
                            'moderate' => 'Moderate',
                            'difficult' => 'Difficult',
                        ],
                        'default_value' => 'easy',
                        'ui' => true,
                    ])
                    ->addLink('link', [
                        'label' => 'Link',
                        'required' => true,
                    ])
                ->endRepeater()
                ->addRepeater('area_links', [
                    'label' => 'Area links',
                    'instructions' => 'Quick links to routes by area, shown below the cards.',
                    'button_label' => 'Add area link',
                    'min' => 0,
                    'layout' => 'block',
                ])
                    ->addLink('link', [
                        'label' => 'Link',
                        'required' => true,
                    ])
                ->endRepeater()
            ->addTab('Link cards', [
                'conditional_logic' => [
                    [
                        [
                            'field' => 'type',
                            'operator' => '==',
                            'value' => 'link',
                        ],
                    ],
                ],
            ])
                ->addRepeater('link_cards', [
                    'label' => 'Cards',
                    'button_label' => 'Add card',
                    'min' => 0,
                    'layout' => 'block',
                ])
                    ->addImage('image', [
                        'label' => 'Image',
                        'return_format' => 'array',
                        'preview_size' => 'thumbnail',
                        'required' => 1,
                    ])
                    ->addText('title', [
                        'label' => 'Title',
                        'required' => 1,
                    ])
                    ->addTextarea('body', [
                        'label' => 'Body text',
                        'rows' => 3,
                        'required' => 1,
                    ])
                    ->addLink('link', [
                        'label' => 'Link',
                        'instructions' => "Link's title is used as the CTA label.",
                        'required' => true,
                    ])
                ->endRepeater();

        return $fields->build();
    }

    /**
     * Retrieve the type.
     *
     * @return string
     */
    public function type()
    {
        return get_field('type') ?: $this->example['type'];
    }

    /**
     * Retrieve the highlighted heading phrase.
     *
     * @return string
     */
    public function headingHighlight()
    {
        return get_field('heading_highlight') ?: $this->example['heading_highlight'];
    }

    /**
     * Retrieve the rest of the heading.
     *
     * @return string
     */
    public function heading()
    {
        return get_field('heading') ?: $this->example['heading'];
    }

    /**
     * Retrieve the intro text (Link type only).
     *
     * @return string|null
     */
    public function intro()
    {
        return $this->type() === 'link' ? (get_field('intro') ?: ($this->example['intro'] ?? null)) : null;
    }

    /**
     * Retrieve the News type's cards.
     *
     * @return array
     */
    public function newsCards()
    {
        return $this->normalizeCards(get_field('news_cards') ?: ($this->example['news_cards'] ?? []));
    }

    /**
     * Retrieve the Route type's cards.
     *
     * @return array
     */
    public function routeCards()
    {
        return $this->normalizeCards(get_field('route_cards') ?: ($this->example['route_cards'] ?? []));
    }

    /**
     * Retrieve the Link type's cards.
     *
     * @return array
     */
    public function linkCards()
    {
        return $this->normalizeCards(get_field('link_cards') ?: ($this->example['link_cards'] ?? []));
    }

    /**
     * Fill in a placeholder image, and a safe `link` shape, for any row
     * missing one — both the fixture data's dead external URL, and a real
     * row with incomplete data: ACF's image field returns `false` (not an
     * array) when unset, and a `link` sub-field can come back as a plain
     * string on legacy/incomplete rows, so `$card['image']['url']` or
     * `$card['link']['target']` would otherwise fatal on a real saved post.
     *
     * @param  array  $cards
     * @return array
     */
    protected function normalizeCards(array $cards)
    {
        $placeholder = Vite::asset('resources/images/placeholder/pattern-placeholder.svg');

        return array_map(function ($card) use ($placeholder) {
            $image = is_array($card['image'] ?? null) ? $card['image'] : [];

if (empty($image['url']) || $image['url'] === 'https://betterbybike.info/wp-content/uploads/placeholder.jpg') {
                $image = ['url' => $placeholder, 'alt' => $image['alt'] ?? ''];
            }

            $card['image'] = $image;
            $card['link'] = $this->normalizeLink($card['link'] ?? null);

            return $card;
        }, $cards);
    }

    /**
     * Coerce a `link` field value to its expected shape — ACF normally
     * returns an array, but a legacy/incomplete row can store a plain
     * string (or nothing at all), which would fatal on array access.
     *
     * @param  mixed  $link
     * @return array
     */
    protected function normalizeLink($link)
    {
        if (is_array($link)) {
            return $link + ['title' => '', 'url' => '#', 'target' => ''];
        }

        return ['title' => '', 'url' => (string) $link, 'target' => ''];
    }

    /**
     * Retrieve the Route type's area quick-links.
     *
     * @return array
     */
    public function areaLinks()
    {
        $rows = get_field('area_links') ?: ($this->example['area_links'] ?? []);

        return array_map(fn ($row) => ['link' => $this->normalizeLink($row['link'] ?? null)], $rows);
    }

    /**
     * Retrieve the bottom "browse all" link (News/Route types only).
     *
     * @return array|null
     */
    public function browseAllLink()
    {
        $link = get_field('browse_all_link') ?: ($this->type() !== 'link' ? ($this->example['browse_all_link'] ?? null) : null);

        return $link ? $this->normalizeLink($link) : null;
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
