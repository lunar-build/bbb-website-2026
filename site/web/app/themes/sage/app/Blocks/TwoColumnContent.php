<?php

namespace App\Blocks;

use App\Fields\Copy;
use App\Fields\Heading;
use Illuminate\Support\Facades\Vite;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class TwoColumnContent extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Two Column Content';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'two-column-content';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A two-column layout — each side independently picks an Image, Text, Quote, Button, or Bullet List.';

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
    public $icon = 'align-pull-left';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'two column',
        'image',
        'quote',
        'button',
        'bullet list',
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
        'left' => [
            'type' => 'image',
        ],
        'right' => [
            'type' => 'text',
            'heading_text' => 'Or plan your own route, simply.',
            'heading_level' => 'h3',
            'heading_style' => 'match',
            'body_text' => 'Find the quickest, quietest or most balanced cycle routes and leisure rides around the West of England. Helping you to discover the best way to get around. Powered by CycleStreets.',
            'body_style' => 'body',
            'link' => ['title' => 'Plan a cycling route', 'url' => '#', 'target' => ''],
        ],
    ];

    /**
     * Component combinations to render stacked on the pattern-library page
     * (see App\View\Composers\PatternLibrary::render()) — each entry is
     * merged onto $example above, so only needs to override what differs.
     *
     * @var array
     */
    public $examples = [
        'Image + Text' => [],
        'Image + Button' => [
            'right' => [
                'type' => 'button',
                'link' => ['title' => 'Link to order online maps', 'url' => '#', 'target' => ''],
            ],
        ],
        // TODO: replace once the dedicated quote-component PR merges — the
        // "quote" layout currently reuses the Heading field as a stand-in
        // for quote text, no citation field yet. See fields() below.
        'Quote + Text (TODO)' => [
            'left' => [
                'type' => 'quote',
                'heading_text' => 'Find the right cycling group for you — there\'s plenty of choice!',
            ],
            'right' => [
                'type' => 'text',
                'heading_text' => '',
                'body_text' => 'Nam eu tortor pellentesque, semper ligula malesuada, posuere arcu. Morbi feugiat imperdiet velit. Proin ac dictum risus.',
            ],
        ],
        'Text + Bullet List' => [
            'left' => [
                'type' => 'text',
                'heading_text' => 'We have bikes for everyone',
                'heading_level' => 'h3',
                'heading_style' => 'match',
                'body_text' => 'As well as our full range of two-wheeled bikes, we stock a range of accessories to help you get the most out of your ride.',
                'body_style' => 'body',
            ],
            'right' => [
                'type' => 'bullet_list',
                'heading' => 'What you get',
                'items' => [
                    ['text' => 'Balance bikes'],
                    ['text' => 'Trailers'],
                    ['text' => 'Bike seats'],
                    ['text' => 'Tag-alongs'],
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
            'left' => $this->slot('left'),
            'right' => $this->slot('right'),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('two_column_content');

        foreach (['left' => 'Left column', 'right' => 'Right column'] as $name => $label) {
            $flexible = $fields->addFlexibleContent($name, [
                'label' => $label,
                'button_label' => 'Choose component',
                'min' => 1,
                'max' => 1,
            ]);

            $flexible->addLayout('image', ['label' => 'Image'])
                ->addImage('image', [
                    'label' => 'Image',
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'required' => 1,
                ]);

            $text = $flexible->addLayout('text', ['label' => 'Text']);
            $text->addPartial(Heading::class, ['name' => 'heading', 'label' => 'Heading', 'default_level' => 'h3']);
            $text->addPartial(Copy::class, ['name' => 'body', 'label' => 'Body copy', 'default_style' => 'body']);
            $text->addLink('link', [
                'label' => 'CTA link',
                'instructions' => 'Optional — shown as a button below the text if filled in.',
            ]);

            // TODO: dedicated quote_text/quote_citation fields, pending the
            // in-flight quote-component PR — reusing Heading as a stand-in
            // for the quote sentence in the meantime, no citation yet.
            $quote = $flexible->addLayout('quote', ['label' => 'Quote']);
            $quote->addPartial(Heading::class, [
                'name' => 'heading',
                'label' => 'Quote text',
                'default_level' => 'h3',
            ]);

            $flexible->addLayout('button', ['label' => 'Button'])
                ->addLink('link', [
                    'label' => 'Link',
                    'required' => true,
                ]);

            $flexible->addLayout('bullet_list', ['label' => 'Bullet List'])
                ->addText('heading', ['label' => 'Heading'])
                ->addRepeater('items', [
                    'label' => 'Items',
                    'button_label' => 'Add item',
                    'min' => 1,
                    'layout' => 'block',
                ])
                    ->addText('text', ['label' => 'Item text', 'required' => 1])
                ->endRepeater();

            $flexible->endFlexibleContent();
        }

        return $fields->build();
    }

    /**
     * Retrieve one column's chosen component, normalized to a
     * `['type' => ..., ...fields]` shape regardless of which layout
     * (image/text/quote/button) was picked.
     *
     * @return array
     */
    protected function slot(string $name): array
    {
        $rows = get_field($name);
        $row = is_array($rows) ? ($rows[0] ?? null) : null;

        if (! $row) {
            $row = $this->example[$name] ?? ['type' => 'text'];
            $row = ['acf_fc_layout' => $row['type']] + $row;
        }

        $type = $row['acf_fc_layout'] ?? 'text';

        return match ($type) {
            'image' => [
                'type' => 'image',
                'image' => is_array($row['image'] ?? null) && ! empty($row['image']['url']) ? $row['image'] : [
                    'url' => Vite::asset('resources/images/placeholder/pattern-placeholder.svg'),
                    'alt' => '',
                ],
            ],
            'quote' => [
                'type' => 'quote',
                'heading' => [
                    'text' => $row['heading_text'] ?? '',
                    'level' => $row['heading_level'] ?? 'h3',
                    'style' => $row['heading_style'] ?? 'match',
                ],
            ],
            'button' => [
                'type' => 'button',
                'link' => $this->normalizeLink($row['link'] ?? null),
            ],
            'bullet_list' => [
                'type' => 'bullet_list',
                'heading' => $row['heading'] ?? '',
                'items' => $row['items'] ?? [],
            ],
            default => [
                'type' => 'text',
                'heading' => [
                    'text' => $row['heading_text'] ?? '',
                    'level' => $row['heading_level'] ?? 'h3',
                    'style' => $row['heading_style'] ?? 'match',
                ],
                'body' => [
                    'text' => $row['body_text'] ?? '',
                    'style' => $row['body_style'] ?? 'body',
                ],
                'link' => $this->normalizeLink($row['link'] ?? null),
            ],
        };
    }

    /**
     * Coerce an ACF `link` field value to its expected shape — ACF normally
     * returns an array, but a legacy/incomplete row can store a plain
     * string (or nothing at all), which would fatal on array access.
     *
     * @param  mixed  $link
     * @return array
     */
    protected function normalizeLink($link)
    {
        if (is_array($link)) {
            return $link + ['title' => '', 'url' => '', 'target' => ''];
        }

        return ['title' => '', 'url' => (string) ($link ?? ''), 'target' => ''];
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
