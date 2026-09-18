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
    public $description = 'A two-column content block, in Image+Text/Image+Button/Quote+Text layouts.';

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
        'layout' => 'image_text',
        'flip' => false,
        'heading_text' => 'Or plan your own route, simply.',
        'heading_level' => 'h3',
        'heading_style' => 'match',
        'body_text' => 'Find the quickest, quietest or most balanced cycle routes and leisure rides around the West of England. Helping you to discover the best way to get around. Powered by CycleStreets.',
        'body_style' => 'body',
        'link' => [
            'title' => 'Plan a cycling route',
            'url' => '#',
            'target' => '',
        ],
    ];

    /**
     * Layout variants to render stacked on the pattern-library page (see
     * App\View\Composers\PatternLibrary::render()) — each entry is merged
     * onto $example above, so only needs to override what differs.
     *
     * @var array
     */
    public $examples = [
        'Image and assorted text' => [],
        'Image and button' => [
            'layout' => 'image_button',
            'heading_text' => '',
            'body_text' => '',
            'link' => ['title' => 'Link to order online maps', 'url' => '#', 'target' => ''],
        ],
        // TODO: replace once the dedicated quote-component PR merges — this
        // layout currently reuses the Heading/Copy fields as a stand-in
        // (citation/quote text), not real quote fields. See two-column-content.blade.php.
        'Quote and text (TODO)' => [
            'layout' => 'quote_text',
            'heading_text' => 'Ben, Cyclists in Bristol',
            'body_text' => 'Find the right cycling group for you — there\'s plenty of choice!',
            'link' => ['title' => '', 'url' => '', 'target' => ''],
        ],
        'Flipped' => [
            'flip' => true,
        ],
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'layout' => $this->layout(),
            'flip' => $this->flip(),
            'image' => $this->image(),
            'heading' => $this->heading(),
            'body' => $this->body(),
            'link' => $this->link(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('two_column_content');

        $fields
            ->addSelect('layout', [
                'label' => 'Layout',
                'instructions' => 'Fields below are shared across every layout — fill in whichever apply and the layout controls how they\'re arranged.',
                'choices' => [
                    'image_text' => 'Image and assorted text',
                    'image_button' => 'Image and button',
                    // TODO: quote layout currently reuses Heading (as
                    // citation) + Copy (as quote text) below, pending a
                    // dedicated quote component.
                    'quote_text' => 'Quote and text',
                ],
                'default_value' => 'image_text',
                'ui' => true,
            ])
            ->addTrueFalse('flip', [
                'label' => 'Flip sides',
                'instructions' => 'Mirror which side the image/quote sits on vs. the text.',
                'default_value' => 0,
                'ui' => true,
            ])
            ->addImage('image', [
                'label' => 'Image',
                'instructions' => 'Shown on the Image and assorted text / Image and button layouts.',
                'return_format' => 'array',
                'preview_size' => 'medium',
            ]);

        $fields->addPartial(Heading::class, [
            'name' => 'heading',
            'label' => 'Heading',
            'default_level' => 'h3',
        ]);

        $fields->addPartial(Copy::class, [
            'name' => 'body',
            'label' => 'Body copy',
            'default_style' => 'body',
        ]);

        $fields
            ->addLink('link', [
                'label' => 'CTA link',
                'instructions' => 'Shown on the Image and assorted text / Image and button layouts. Leave empty for no CTA.',
            ]);

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
     * Retrieve whether the layout should be flipped.
     *
     * @return bool
     */
    public function flip()
    {
        $value = get_field('flip');

        return $value !== null ? (bool) $value : (bool) ($this->example['flip'] ?? false);
    }

    /**
     * Retrieve the image (Image and assorted text / Image and button layouts).
     *
     * @return array|null
     */
    public function image()
    {
        return get_field('image') ?: [
            'url' => Vite::asset('resources/images/placeholder/pattern-placeholder.svg'),
            'alt' => '',
        ];
    }

    /**
     * Retrieve the heading text/level/style.
     *
     * @return array
     */
    public function heading()
    {
        return [
            'text' => get_field('heading_text') ?: ($this->example['heading_text'] ?? ''),
            'level' => get_field('heading_level') ?: ($this->example['heading_level'] ?? 'h3'),
            'style' => get_field('heading_style') ?: ($this->example['heading_style'] ?? 'match'),
        ];
    }

    /**
     * Retrieve the body copy text/style.
     *
     * @return array
     */
    public function body()
    {
        return [
            'text' => get_field('body_text') ?: ($this->example['body_text'] ?? ''),
            'style' => get_field('body_style') ?: ($this->example['body_style'] ?? 'body'),
        ];
    }

    /**
     * Retrieve the CTA link.
     *
     * @return array
     */
    public function link()
    {
        $link = get_field('link') ?: ($this->example['link'] ?? null);

        return is_array($link) ? $link + ['title' => '', 'url' => '', 'target' => ''] : ['title' => '', 'url' => '', 'target' => ''];
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
