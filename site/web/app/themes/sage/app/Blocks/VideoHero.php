<?php

namespace App\Blocks;

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
    public $description = 'A video masthead hero with heading, intro text, and a primary call-to-action.';

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
    public $mode = 'preview';

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
    public $styles = ['light', 'dark'];

    /**
     * The block preview example data.
     *
     * @var array
     */
    public $example = [
        'heading' => 'Cycling in the West of England',
        'intro' => "If you haven't ridden a bike in years, or have never cycled at all – we can help you find all the resources you need to ride a bike for commuting, health, fitness and fun.",
        'video_alt' => 'Video montage of cyclists in the South West.',
        'cta_label' => 'Try our cycle planner',
        'cta_subtext' => 'Find a route that suits you!',
        'cta_button_text' => 'Plan a route',
        'cta_button_url' => 'https://cycleplanner.betterbybike.info',
    ];

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
            'ctaLabel' => $this->ctaLabel(),
            'ctaSubtext' => $this->ctaSubtext(),
            'ctaButton' => $this->ctaButton(),
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
                ])
            ->addTab('Call to Action')
                ->addText('cta_label', [
                    'label' => 'CTA label',
                    'instructions' => 'Small bold label above the button, e.g. "Try our cycle planner".',
                ])
                ->addText('cta_subtext', [
                    'label' => 'CTA subtext',
                ])
                ->addLink('cta_button', [
                    'label' => 'CTA button',
                    'instructions' => 'Button text + URL (+ optional "open in new tab").',
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
        return get_field('video');
    }

    /**
     * Retrieve the poster image.
     *
     * @return array|null
     */
    public function poster()
    {
        return get_field('poster');
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
     * Retrieve the CTA label.
     *
     * @return string
     */
    public function ctaLabel()
    {
        return get_field('cta_label') ?: $this->example['cta_label'];
    }

    /**
     * Retrieve the CTA subtext.
     *
     * @return string
     */
    public function ctaSubtext()
    {
        return get_field('cta_subtext') ?: $this->example['cta_subtext'];
    }

    /**
     * Retrieve the CTA button link.
     *
     * @return array
     */
    public function ctaButton()
    {
        return get_field('cta_button') ?: [
            'title' => $this->example['cta_button_text'],
            'url' => $this->example['cta_button_url'],
            'target' => '_blank',
        ];
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
