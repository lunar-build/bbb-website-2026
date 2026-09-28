<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class LinkList extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Link List';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'link-list';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A repeater of plain links, grouped by an optional icon or shown with a short description.';

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
    public $icon = 'editor-ul';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'link',
        'list',
        'short-form',
        'long-form',
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
        'layout' => 'short_centred',
        'icon' => 'cycling-route',
        'links' => [
            ['description' => '', 'link' => ['title' => 'Bristol Cycle routes', 'url' => '#', 'target' => '']],
            ['description' => '', 'link' => ['title' => 'Bath & NE Somerset cycle routes', 'url' => '#', 'target' => '']],
            ['description' => '', 'link' => ['title' => 'North Somerset cycle routes', 'url' => '#', 'target' => '']],
            ['description' => '', 'link' => ['title' => 'South Glos Cycle routes', 'url' => '#', 'target' => '']],
        ],
    ];

    /**
     * Type variants to render stacked on the pattern-library page (see
     * App\View\Composers\PatternLibrary::render()) — each entry is merged
     * onto $example above, so only needs to override what differs.
     *
     * @var array
     */
    public $examples = [
        'Short-form, centred' => [],
        'Short-form, left align' => [
            'layout' => 'short_left',
        ],
        'Long-form, with text' => [
            'layout' => 'long_with_text',
            'icon' => '',
            'links' => [
                [
                    'description' => 'Caters for a wide variety of cycling interests and a full range of ability and age.',
                    'link' => ['title' => 'Bath Cycling club', 'url' => '#', 'target' => ''],
                ],
                [
                    'description' => 'A social cycling community, launched in February 2026. Whether you are a seasoned cyclist or haven\'t been on a bike in years, join a "Bimbles" — rides where we prioritise friendship and fun over speed and endurance.',
                    'link' => ['title' => 'Bitton and Oldland Cycling Club', 'url' => '#', 'target' => ''],
                ],
                [
                    'description' => 'Have Sunday runs starting from Clevedon Triangle all year.',
                    'link' => ['title' => 'Clevedon and District Road Club', 'url' => '#', 'target' => ''],
                ],
            ],
        ],
        'Long-form, just links' => [
            'layout' => 'long_just_links',
            'icon' => '',
            'links' => [
                ['description' => '', 'link' => ['title' => 'Bath Cycling club', 'url' => '#', 'target' => '']],
                ['description' => '', 'link' => ['title' => 'Bitton and Oldland Cycling Club', 'url' => '#', 'target' => '']],
                ['description' => '', 'link' => ['title' => 'Clevedon and District Road Club', 'url' => '#', 'target' => '']],
            ],
        ],
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'layout' => $this->layout(),
            'icon' => $this->icon(),
            'links' => $this->links(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('link_list');

        $fields
            ->addTab('Content')
                ->addSelect('layout', [
                    'label' => 'Layout',
                    'choices' => [
                        'short_centred' => 'Short-form, centred',
                        'short_left' => 'Short-form, left align',
                        'long_with_text' => 'Long-form, with text',
                        'long_just_links' => 'Long-form, just links',
                    ],
                    'default_value' => 'short_centred',
                    'ui' => true,
                ])
            ->addTab('Short-form, centred', [
                'conditional_logic' => [
                    [
                        [
                            'field' => 'layout',
                            'operator' => '==',
                            'value' => 'short_centred',
                        ],
                    ],
                ],
            ])
                ->addSelect('short_centred_icon', [
                    'label' => 'Icon',
                    'instructions' => 'Optional — shown once above the whole list.',
                    'choices' => svg_icon_choices(),
                    'allow_null' => true,
                    'ui' => 1,
                    'placeholder' => 'None',
                ])
                ->addRepeater('short_centred_links', [
                    'label' => 'Links',
                    'button_label' => 'Add link',
                    'min' => 1,
                    'layout' => 'block',
                ])
                    ->addLink('link', [
                        'label' => 'Link',
                        'instructions' => "Link's title is used as the link text.",
                        'required' => true,
                    ])
                ->endRepeater()
            ->addTab('Short-form, left align', [
                'conditional_logic' => [
                    [
                        [
                            'field' => 'layout',
                            'operator' => '==',
                            'value' => 'short_left',
                        ],
                    ],
                ],
            ])
                ->addSelect('short_left_icon', [
                    'label' => 'Icon',
                    'instructions' => 'Optional — shown once above the whole list.',
                    'choices' => svg_icon_choices(),
                    'allow_null' => true,
                    'ui' => 1,
                    'placeholder' => 'None',
                ])
                ->addRepeater('short_left_links', [
                    'label' => 'Links',
                    'button_label' => 'Add link',
                    'min' => 1,
                    'layout' => 'block',
                ])
                    ->addLink('link', [
                        'label' => 'Link',
                        'instructions' => "Link's title is used as the link text.",
                        'required' => true,
                    ])
                ->endRepeater()
            ->addTab('Long-form, with text', [
                'conditional_logic' => [
                    [
                        [
                            'field' => 'layout',
                            'operator' => '==',
                            'value' => 'long_with_text',
                        ],
                    ],
                ],
            ])
                ->addRepeater('long_with_text_links', [
                    'label' => 'Links',
                    'button_label' => 'Add link',
                    'min' => 1,
                    'layout' => 'block',
                ])
                    ->addTextarea('description', [
                        'label' => 'Description',
                        'rows' => 2,
                    ])
                    ->addLink('link', [
                        'label' => 'Link',
                        'instructions' => "Link's title is used as the link text.",
                        'required' => true,
                    ])
                ->endRepeater()
            ->addTab('Long-form, just links', [
                'conditional_logic' => [
                    [
                        [
                            'field' => 'layout',
                            'operator' => '==',
                            'value' => 'long_just_links',
                        ],
                    ],
                ],
            ])
                ->addRepeater('long_just_links_links', [
                    'label' => 'Links',
                    'button_label' => 'Add link',
                    'min' => 1,
                    'layout' => 'block',
                ])
                    ->addLink('link', [
                        'label' => 'Link',
                        'instructions' => "Link's title is used as the link text.",
                        'required' => true,
                    ])
                ->endRepeater();

        return $fields->build();
    }

    /**
     * Retrieve the layout.
     *
     * @return string
     */
    public function layout()
    {
        return get_field('layout') ?: $this->example['layout'];
    }

    /**
     * Retrieve the single icon shown above the whole list — short-form
     * layouts only, one per list rather than one per link.
     *
     * @return string
     */
    public function icon()
    {
        $field = match ($this->layout()) {
            'short_centred' => 'short_centred_icon',
            'short_left' => 'short_left_icon',
            default => null,
        };

        if (! $field) {
            return '';
        }

        return get_field($field) ?: $this->example['icon'];
    }

    /**
     * Retrieve the links, from the repeater matching the selected layout —
     * each layout has its own repeater/field set (see fields()) so editors
     * only ever see fields relevant to their chosen layout.
     *
     * @return array
     */
    public function links()
    {
        $field = match ($this->layout()) {
            'short_centred' => 'short_centred_links',
            'short_left' => 'short_left_links',
            'long_with_text' => 'long_with_text_links',
            'long_just_links' => 'long_just_links_links',
            default => 'short_centred_links',
        };

        $rows = get_field($field) ?: $this->example['links'];

        return array_map(fn($row) => [
            'description' => $row['description'] ?? '',
            'link' => $this->normalizeLink($row['link'] ?? null),
        ], $rows);
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
     * Assets enqueued with 'enqueue_block_assets' when rendering the block.
     *
     * @link https://developer.wordpress.org/block-editor/how-to-guides/enqueueing-assets-in-the-editor/#editor-content-scripts-and-styles
     */
    public function assets(array $block): void
    {
        //
    }
}
