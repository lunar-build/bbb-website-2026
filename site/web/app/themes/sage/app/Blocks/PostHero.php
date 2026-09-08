<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class PostHero extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Post Hero';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A post/article hero: date, share links, title, and category pills — pulled from the current post.';

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
    public $icon = 'admin-post';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'post',
        'news',
        'article',
        'hero',
        'share',
        'category',
    ];

    /**
     * The block post type allow list.
     *
     * @var array
     */
    public $post_types = ['post'];

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
        'multiple' => false,
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
     * The block preview example data — stands in when there's no real post
     * in context (e.g. the pattern-library page).
     *
     * @var array
     */
    public $example = [
        'date' => '30th July 2026',
        'title' => 'Making it easier and safer to walk, wheel and cycle across Bath',
        'permalink' => 'https://betterbybike.info/making-it-easier-and-safer-to-walk-wheel-and-cycle-across-bath/',
        'categories' => [
            ['label' => 'Bath & NE Somerset', 'url' => '#'],
            ['label' => 'Infrastructure', 'url' => '#'],
            ['label' => 'News', 'url' => '#'],
            ['label' => 'Region', 'url' => '#'],
            ['label' => 'Safety Schemes', 'url' => '#'],
        ],
    ];

    /**
     * This block has no ACF fields of its own — date/title/categories are
     * read directly from the current post, the same "pull from live
     * context, fall back to $example" convention App\Support\Breadcrumbs
     * already uses.
     */
    public function with(): array
    {
        return [
            'date' => $this->date(),
            'title' => $this->title(),
            'permalink' => $this->permalink(),
            'categories' => $this->categories(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('post_hero');

        return $fields->build();
    }

    /**
     * Whether we're rendering inside a real blog post — not just any
     * truthy post ID, since this block also renders standalone on the
     * pattern-library page, which is itself a real (but wrong) `page`
     * post in the loop.
     *
     * @return bool
     */
    protected function inPostContext()
    {
        return get_post_type() === 'post';
    }

    /**
     * Retrieve the post's published date.
     *
     * @return string
     */
    public function date()
    {
        return $this->inPostContext() ? get_the_date() : $this->example['date'];
    }

    /**
     * Retrieve the post title.
     *
     * @return string
     */
    public function title()
    {
        return $this->inPostContext() ? get_the_title() : $this->example['title'];
    }

    /**
     * Retrieve the post permalink (used to build share links).
     *
     * @return string
     */
    public function permalink()
    {
        return $this->inPostContext() ? get_permalink() : $this->example['permalink'];
    }

    /**
     * Retrieve the post's categories as [['label' => ..., 'url' => ...]],
     * suitable for <x-pill>.
     *
     * @return array
     */
    public function categories()
    {
        if (! $this->inPostContext()) {
            return $this->example['categories'];
        }

        $categories = get_the_category();

        if (empty($categories)) {
            return [];
        }

        return array_map(fn($category) => [
            'label' => $category->name,
            'url' => get_category_link($category),
        ], $categories);
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
