<?php

namespace App\Blocks;

use App\Blocks\Concerns\ResolvesFields;
use App\Fields\Copy;
use App\Fields\Heading;
use App\Support\PostCard;
use Illuminate\Support\Facades\Vite;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class ChildPageCards extends Block
{
    use ResolvesFields;

    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Child Page Cards';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'child-page-cards';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A grid of link cards for every published child page of the current page, kept in sync automatically as pages are added, removed or reordered.';

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
    public $icon = 'networking';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'child',
        'pages',
        'cards',
        'subpages',
    ];

    /**
     * The block post type allow list.
     *
     * @var array
     */
    public $post_types = ['page'];

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
     * The block preview example data.
     *
     * @var array
     */
    public $example = [
        'heading_text' => 'Find the right bike',
        'heading_level' => 'h2',
        'heading_style' => 'match',
        'intro_text' => 'Everything you need to choose a bike that suits you.',
        'intro_style' => 'body',
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'heading' => $this->heading(),
            'intro' => $this->intro(),
            'cards' => $this->cards(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('child_page_cards');

        $fields->addMessage('child_pages_note', 'Cards are built from this page\'s published child pages (Page Attributes → Parent), in their Order. Each card uses the child\'s featured image, title and excerpt.', [
            'label' => 'Cards',
        ]);

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

        return $fields->build();
    }

    public function heading()
    {
        return [
            'text' => $this->resolveField('heading_text', ''),
            'level' => $this->resolveField('heading_level', 'h2'),
            'style' => $this->resolveField('heading_style', 'match'),
        ];
    }

    public function intro()
    {
        return [
            'text' => $this->resolveField('intro_text', ''),
            'style' => $this->resolveField('intro_style', 'body'),
        ];
    }

    /**
     * Link cards for the current page's published children. The pattern library
     * has no real page, so it gets placeholder cards instead.
     */
    public function cards(): array
    {
        $parent = (int) ($this->post_id ?: get_the_ID());
        $children = $parent ? get_pages([
            'parent' => $parent,
            'sort_column' => 'menu_order,post_title',
            'post_status' => 'publish',
        ]) : [];

        if (! $children) {
            return $this->exampleField('cards', []) ? $this->exampleCards() : [];
        }

        return array_map(function (\WP_Post $page) {
            $card = PostCard::fromPost($page, 'match');

            return [...$card, 'date' => null, 'categories' => []];
        }, $children);
    }

    /**
     * Pattern-library cards, shaped like PostCard::fromPost()'s output.
     */
    protected function exampleCards(): array
    {
        $image = ['url' => Vite::asset('resources/images/placeholder/pattern-placeholder.svg'), 'alt' => ''];

        return array_map(fn($title) => [
            'image' => $image,
            'date' => null,
            'categories' => [],
            'heading' => ['text' => $title, 'level' => 'h3', 'style' => 'match'],
            'body' => ['text' => 'A short summary of what this page covers.', 'style' => 'body'],
            'link' => ['url' => '#', 'title' => $title, 'label' => __('Find out more', 'sage')],
        ], $this->exampleField('cards', []));
    }

    /**
     * Example card titles live under 'cards' so exampleField() only returns
     * them on the pattern library.
     */
    public function example(): array
    {
        return ['cards' => ['Types of bike', 'Choosing the right size', 'E-bikes']];
    }
}
