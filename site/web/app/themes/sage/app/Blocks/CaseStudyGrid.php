<?php

namespace App\Blocks;

use Illuminate\Support\Facades\Vite;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class CaseStudyGrid extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Case Study Grid';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'case-study-grid';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'An offset two-column grid of case-study cards — an image with a quote always visible underneath, no interaction needed to read it.';

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
        'case study',
        'case studies',
        'quote',
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
                'image' => ['url' => '', 'alt' => ''],
                'quote' => 'Wow – they\'ve thought of everything… love that there are lights in case I forget and need to borrow them to get home',
            ],
            [
                'image' => ['url' => '', 'alt' => ''],
                'quote' => 'It makes me feel much more comfortable cycling to work as I know the support is there if I need it',
            ],
            [
                'image' => ['url' => '', 'alt' => ''],
                'quote' => 'What a great idea! I\'ll be looking that out if I need it!',
            ],
            [
                'image' => ['url' => '', 'alt' => ''],
                'quote' => 'What a great idea! I\'ll be looking that out if I need it!',
            ],
        ],
    ];

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
        $fields = Builder::make('case_study_grid');

        $cards = $fields->addRepeater('cards', [
            'label' => 'Cards',
            'button_label' => 'Add card',
            'min' => 0,
            'layout' => 'block',
        ]);

        $cards
            ->addImage('image', [
                'label' => 'Image',
                'return_format' => 'array',
                'preview_size' => 'medium',
                'required' => 1,
            ])
            ->addTextarea('quote', [
                'label' => 'Quote',
                'required' => 1,
                'rows' => 3,
            ]);

        $cards->endRepeater();

        return $fields->build();
    }

    /**
     * Retrieve the cards, normalized to a safe shape with a placeholder
     * image fallback.
     *
     * @return array
     */
    public function cards()
    {
        $placeholder = Vite::asset('resources/images/placeholder/pattern-placeholder.svg');

        $cards = get_field('cards') ?: ($this->example['cards'] ?? []);

        return array_map(function ($card) use ($placeholder) {
            $image = is_array($card['image'] ?? null) ? $card['image'] : [];

            if (empty($image['url'])) {
                $image = ['url' => $placeholder, 'alt' => $image['alt'] ?? ''];
            }

            return [
                'image' => $image,
                'quote' => $card['quote'] ?? '',
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
