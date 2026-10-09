<?php

namespace App\Blocks;

use App\Fields\Heading;
use Illuminate\Support\Facades\Vite;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class VideoBlock extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Video Block';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'video-block';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'An inline video with a poster and play/pause button, and a title row with a transcript download underneath.';

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
        'transcript',
        'download',
        'media',
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
        'heading_text' => 'Video title goes here',
        'heading_level' => 'h3',
        'heading_style' => 'h4',
        'transcript_label' => 'Download transcript',
        'transcript_file_size' => 'PDF 1.1MB',
    ];

    /**
     * Fixture data that can't be a constant expression (Vite::asset()).
     */
    public function example(): array
    {
        return [
            'video' => ['url' => Vite::asset('resources/videos/placeholder/pattern-placeholder.mp4')],
            'poster' => ['url' => Vite::asset('resources/images/placeholder/pattern-placeholder.svg')],
            'transcript' => ['url' => '#', 'filename' => 'video-transcript.pdf'],
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
            'heading' => $this->heading(),
            'transcript' => $this->transcript(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('video_block');

        $fields
            ->addFile('video', [
                'label' => 'Video file',
                'instructions' => 'Upload an MP4.',
                'return_format' => 'array',
                'library' => 'all',
                'mime_types' => 'mp4',
                'required' => 1,
            ])
            ->addImage('poster', [
                'label' => 'Poster image',
                'instructions' => 'Shown before the video plays. Leave empty to use the video\'s first frame.',
                'return_format' => 'array',
                'preview_size' => 'medium',
            ]);

        $fields->addPartial(Heading::class, [
            'name' => 'heading',
            'label' => 'Video title',
            'default_level' => 'h3',
            'default_style' => 'h4',
            'required' => true,
        ]);

        $fields
            ->addFile('transcript_file', [
                'label' => 'Transcript file',
                'instructions' => 'The accessible alternative to the video — a document of everything spoken and shown.',
                'return_format' => 'array',
                'required' => 1,
            ])
            ->addText('transcript_label', [
                'label' => 'Transcript button label',
                'default_value' => 'Download transcript',
                'required' => 1,
            ])
            ->addText('transcript_file_size', [
                'label' => 'Transcript file size label',
                'instructions' => 'e.g. "PDF 1.1MB".',
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
     * Retrieve the poster image. Null is valid — the video then shows its own first frame.
     *
     * @return array|null
     */
    public function poster()
    {
        $video = get_field('video');

        if ($video) {
            return get_field('poster') ?: null;
        }

        return $this->example['poster'] ?? null;
    }

    /**
     * Retrieve the video title text/level/style.
     *
     * @return array
     */
    public function heading()
    {
        return [
            'text' => get_field('heading_text') ?: $this->example['heading_text'],
            'level' => get_field('heading_level') ?: $this->example['heading_level'],
            'style' => get_field('heading_style') ?: $this->example['heading_style'],
        ];
    }

    /**
     * Retrieve the transcript download's file/label/size.
     *
     * @return array
     */
    public function transcript()
    {
        if (! get_field('video')) {
            return [
                'url' => $this->example['transcript']['url'] ?? '#',
                'label' => $this->example['transcript_label'],
                'file_size' => $this->example['transcript_file_size'],
            ];
        }

        $file = get_field('transcript_file');

        return [
            'url' => is_array($file) ? ($file['url'] ?? null) : null,
            'label' => get_field('transcript_label') ?: 'Download transcript',
            'file_size' => get_field('transcript_file_size') ?: '',
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
