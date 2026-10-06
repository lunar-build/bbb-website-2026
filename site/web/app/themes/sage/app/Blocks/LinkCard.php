<?php

namespace App\Blocks;

use Illuminate\Support\Facades\Vite;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class LinkCard extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Link Card';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'link-card';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A repeater of bordered link cards, each linking out or offering a file download.';

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
    public $icon = 'admin-links';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'link',
        'card',
        'download',
        'transcript',
        'list',
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
        'cards' => [
            [
                'logo' => true,
                'title' => 'Report a non-urgent issue',
                'description' => 'Help keep yourself and others safe by reporting any road or street faults you see on your cycle routes to the relevant local authority.',
                'cta_type' => 'link',
                'link' => ['title' => 'Report', 'url' => '#', 'target' => ''],
            ],
            [
                'logo' => null,
                'title' => '',
                'description' => 'For more information and advice visit Avon & Somerset Police',
                'cta_type' => 'link',
                'link' => ['title' => 'Visit website', 'url' => '#', 'target' => ''],
            ],
            [
                'logo' => true,
                'title' => 'City of Bath',
                'description' => 'Cycle routes in the Bath area.',
                'cta_type' => 'download',
                'file' => ['url' => '#', 'filename' => 'city-of-bath-cycle-map.pdf'],
                'cta_label' => 'Download map',
                'file_size' => 'PDF 1.1MB',
            ],
        ],
    ];

    /**
     * Swap the `true` logo placeholders in the static example above for a
     * computed placeholder asset URL (Vite::asset isn't available yet when
     * the static property is declared).
     *
     * @return array
     */
    public function example(): array
    {
        $placeholder = ['url' => Vite::asset('resources/images/placeholder/pattern-placeholder.svg'), 'alt' => ''];

        $cards = array_map(function ($card) use ($placeholder) {
            $card['logo'] = $card['logo'] ? $placeholder : null;

            return $card;
        }, $this->example['cards']);

        return ['cards' => $cards];
    }

    public function with(): array
    {
        return [
            'cards' => $this->cards(),
        ];
    }

    public function fields(): array
    {
        $fields = Builder::make('link_card');

        $fields
            ->addTab('Content')
                ->addRepeater('cards', [
                    'label' => 'Cards',
                    'button_label' => 'Add card',
                    'min' => 1,
                    'layout' => 'block',
                ])
                    ->addImage('logo', [
                        'label' => 'Logo',
                        'instructions' => 'Optional. Leave empty for a plain (no logo) card.',
                        'return_format' => 'array',
                        'preview_size' => 'thumbnail',
                        'required' => 0,
                    ])
                    ->addText('title', [
                        'label' => 'Title',
                        'instructions' => 'Optional when the description alone says enough (e.g. a short prompt card).',
                    ])
                    ->addTextarea('description', [
                        'label' => 'Description',
                        'rows' => 3,
                    ])
                    ->addSelect('cta_type', [
                        'label' => 'CTA type',
                        'choices' => [
                            'link' => 'Link',
                            'download' => 'Download',
                        ],
                        'default_value' => 'link',
                        'ui' => true,
                    ])
                    ->addLink('link', [
                        'label' => 'Link',
                        'instructions' => "Link's title is used as the CTA label.",
                        'required' => true,
                        'conditional_logic' => [
                            [
                                [
                                    'field' => 'cta_type',
                                    'operator' => '==',
                                    'value' => 'link',
                                ],
                            ],
                        ],
                    ])
                    ->addFile('file', [
                        'label' => 'File',
                        'return_format' => 'array',
                        'required' => true,
                        'conditional_logic' => [
                            [
                                [
                                    'field' => 'cta_type',
                                    'operator' => '==',
                                    'value' => 'download',
                                ],
                            ],
                        ],
                    ])
                    ->addText('cta_label', [
                        'label' => 'Download button label',
                        'instructions' => 'e.g. "Download map" or "Download transcript".',
                        'default_value' => 'Download',
                        'required' => true,
                        'conditional_logic' => [
                            [
                                [
                                    'field' => 'cta_type',
                                    'operator' => '==',
                                    'value' => 'download',
                                ],
                            ],
                        ],
                    ])
                    ->addText('file_size', [
                        'label' => 'File size label',
                        'instructions' => 'e.g. "PDF 1.1MB".',
                        'conditional_logic' => [
                            [
                                [
                                    'field' => 'cta_type',
                                    'operator' => '==',
                                    'value' => 'download',
                                ],
                            ],
                        ],
                    ])
                ->endRepeater();

        return $fields->build();
    }

    /**
     * @return array
     */
    public function cards()
    {
        $rows = get_field('cards') ?: $this->example['cards'];

        return array_map(fn($card) => $this->normalizeCard($card), $rows);
    }

    /**
     * Normalize a single card row so the view never has to guard against
     * ACF's empty/legacy field shapes (e.g. a `link` sub-field coming back
     * as a plain string, or a missing logo/file).
     *
     * @param  array  $card
     * @return array
     */
    protected function normalizeCard(array $card)
    {
        $logo = is_array($card['logo'] ?? null) ? $card['logo'] : null;

        $card['logo'] = $logo;
        $card['cta_type'] = ($card['cta_type'] ?? 'link') === 'download' ? 'download' : 'link';

        if ($card['cta_type'] === 'download') {
            $card['file'] = is_array($card['file'] ?? null) ? $card['file'] : ['url' => '#', 'filename' => ''];
            $card['cta_label'] = $card['cta_label'] ?? 'Download';
            $card['file_size'] = $card['file_size'] ?? '';
        } else {
            $card['link'] = $this->normalizeLink($card['link'] ?? null);
        }

        return $card;
    }

    /**
     * Coerce a `link` field value to its expected shape — ACF normally
     * returns an array, but a legacy/incomplete row can store a plain
     * string (or nothing at all), which would fatal on array access.
     *
     * An empty title would otherwise render an `<a>` with no accessible
     * name (the arrow icon is aria-hidden) — fall back to a generic label
     * so the link always announces its purpose (WCAG 2.4.4).
     *
     * @param  mixed  $link
     * @return array
     */
    protected function normalizeLink($link)
    {
        if (is_array($link)) {
            $link = $link + ['title' => '', 'url' => '#', 'target' => ''];
        } else {
            $link = ['title' => '', 'url' => (string) $link, 'target' => ''];
        }

        $link['title'] = $link['title'] ?: 'Visit website';

        return $link;
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
