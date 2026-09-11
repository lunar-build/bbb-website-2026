<?php

namespace App\Blocks;

use Illuminate\Support\Facades\Vite;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class VideoHero extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Video Hero';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'The main/homepage hero: full-width video masthead, heading + intro text below it, and a widget slot (e.g. the Journey Planner Widget) overlapping the boundary between them.';

    /**
     * The block category.
     *
     * @var string
     */
    public $category = 'media';

    /**
     * The block icon.
     *
     * @var string|array
     */
    public $icon = 'video-alt3';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'video',
        'hero',
        'masthead',
        'cta',
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
    public $align = 'full';

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
     * The widget slot's InnerBlocks template — pre-populates a Journey
     * Planner Widget when the hero is first inserted. Set the widget's own
     * "Wrap in container" (`contained`) field off when using it here, since
     * this hero already provides `.o-container`.
     *
     * @var array
     */
    public $template = [
        'acf/journey-planner-widget' => [],
    ];

    /**
     * Blocks allowed in the widget slot — scoped to the Journey Planner
     * Widget for now (the slot is styled/positioned as a widget card, not a
     * general content area). Add another block name here if a second
     * widget-shaped block is ever built for this slot.
     *
     * @var array
     */
    public $allowedBlocks = [
        'acf/journey-planner-widget',
    ];

    /**
     * The block preview example data.
     *
     * @var array
     */
    public $example = [
        'heading' => 'Cycling in the West of England',
        'intro' => "If you haven't ridden a bike in years, or have never cycled at all – we can help you find all the resources you need to ride a bike for commuting, health, fitness and fun.",
        'video_alt' => 'Video montage of cyclists in the South West.',
    ];

    /**
     * Merge computed placeholder asset URLs into the block preview example data.
     *
     * @return array
     */
    public function example(): array
    {
        return [
            'video' => ['url' => Vite::asset('resources/videos/placeholder/pattern-placeholder.mp4')],
            'poster' => ['url' => Vite::asset('resources/images/placeholder/pattern-placeholder.svg')],
        ];
    }

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'video' => $this->video(),
            'poster' => $this->poster(),
            'videoAlt' => $this->videoAlt(),
            'heading' => $this->heading(),
            'intro' => $this->intro(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('video_hero');

        $fields
            ->addTab('Video')
                ->addFile('video', [
                    'label' => 'Video file',
                    'instructions' => 'Upload an MP4. Muted, autoplaying, looped background video.',
                    'return_format' => 'array',
                    'library' => 'all',
                    'mime_types' => 'mp4',
                ])
                ->addImage('poster', [
                    'label' => 'Poster / placeholder image',
                    'instructions' => 'Shown before the video loads and as a fallback poster frame.',
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                ])
                ->addText('video_alt', [
                    'label' => 'Video accessible label',
                    'instructions' => 'aria-label describing the video for screen reader users.',
                ])
            ->addTab('Content')
                ->addText('heading', [
                    'label' => 'Heading',
                ])
                ->addTextarea('intro', [
                    'label' => 'Intro text',
                    'rows' => 3,
                    'new_lines' => 'br',
                ]);

        return $fields->build();
    }

    /**
     * Retrieve the video file.
     *
     * @return array|null
     */
    public function video()
    {
        return get_field('video') ?: ($this->example['video'] ?? null);
    }

    /**
     * Retrieve the poster image.
     *
     * @return array|null
     */
    public function poster()
    {
        return get_field('poster') ?: ($this->example['poster'] ?? null);
    }

    /**
     * Retrieve the video accessible label.
     *
     * @return string
     */
    public function videoAlt()
    {
        return get_field('video_alt') ?: $this->example['video_alt'];
    }

    /**
     * Retrieve the heading.
     *
     * @return string
     */
    public function heading()
    {
        return get_field('heading') ?: $this->example['heading'];
    }

    /**
     * Retrieve the intro text.
     *
     * @return string
     */
    public function intro()
    {
        return get_field('intro') ?: $this->example['intro'];
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
