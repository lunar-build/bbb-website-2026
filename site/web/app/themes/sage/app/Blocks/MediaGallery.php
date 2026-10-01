<?php

namespace App\Blocks;

use Illuminate\Support\Facades\Vite;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class MediaGallery extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Media Gallery';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A dynamic masonry-style grid of images/videos, each editor-sized as 1 or 2 columns wide. Paginates into a carousel once the grid would run past 3 rows; every tile opens its full media in an accessible lightbox.';

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
    public $icon = 'grid-view';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'gallery',
        'media',
        'grid',
        'carousel',
        'lightbox',
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
     * Maximum column-units per carousel page: 4 grid columns × 3 rows.
     *
     * @var int
     */
    const PAGE_CAPACITY = 12;

    /**
     * The block preview example data — 9 items reproducing the Figma
     * reference's masonry layout exactly (4 squares / 1+wide+1 / 2 wides),
     * to demonstrate the dynamic grid at its densest documented shape.
     *
     * @var array
     */
    public $example = [
        'items' => [
            ['media_type' => 'image', 'alt' => 'Gallery image 1', 'column_span' => 1],
            ['media_type' => 'image', 'alt' => 'Gallery image 2', 'column_span' => 1],
            ['media_type' => 'image', 'alt' => 'Gallery image 3', 'column_span' => 1],
            ['media_type' => 'video', 'alt' => 'Gallery video 1', 'column_span' => 1],
            ['media_type' => 'video', 'alt' => 'Gallery video 2', 'column_span' => 1],
            ['media_type' => 'image', 'alt' => 'Gallery image 4', 'column_span' => 2],
            ['media_type' => 'image', 'alt' => 'Gallery image 5', 'column_span' => 1],
            ['media_type' => 'image', 'alt' => 'Gallery image 6', 'column_span' => 2],
            ['media_type' => 'image', 'alt' => 'Gallery image 7', 'column_span' => 2],
        ],
    ];

    /**
     * Fallback example data requiring a non-constant expression (Vite::asset).
     *
     * @return array
     */
    public function example(): array
    {
        $image = ['url' => Vite::asset('resources/images/placeholder/pattern-placeholder.svg'), 'alt' => ''];
        $video = ['url' => Vite::asset('resources/videos/placeholder/pattern-placeholder.mp4')];

        $items = array_map(function ($item) use ($image, $video) {
            $item['image'] = $image;
            $item['video_poster'] = $image;
            $item['video'] = $video;

            return $item;
        }, $this->example['items']);

        return ['items' => $items];
    }

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'pages' => $this->pages(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('media_gallery');

        $imageCondition = [
            [
                [
                    'field' => 'media_type',
                    'operator' => '==',
                    'value' => 'image',
                ],
            ],
        ];

        $videoCondition = [
            [
                [
                    'field' => 'media_type',
                    'operator' => '==',
                    'value' => 'video',
                ],
            ],
        ];

        $fields
            ->addRepeater('items', [
                'label' => 'Items',
                'button_label' => 'Add item',
                'min' => 1,
                'layout' => 'block',
            ])
                ->addSelect('media_type', [
                    'label' => 'Media type',
                    'choices' => [
                        'image' => 'Image',
                        'video' => 'Video',
                    ],
                    'default_value' => 'image',
                    'ui' => true,
                ])
                ->addImage('image', [
                    'label' => 'Image',
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'required' => 1,
                    'conditional_logic' => $imageCondition,
                ])
                ->addFile('video', [
                    'label' => 'Video file',
                    'instructions' => 'Upload an MP4 — played in the lightbox when the tile is opened.',
                    'return_format' => 'array',
                    'library' => 'all',
                    'mime_types' => 'mp4',
                    'required' => 1,
                    'conditional_logic' => $videoCondition,
                ])
                ->addImage('video_poster', [
                    'label' => 'Video poster / thumbnail',
                    'instructions' => 'Shown in the grid tile before the video is opened.',
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'required' => 1,
                    'conditional_logic' => $videoCondition,
                ])
                ->addText('alt', [
                    'label' => 'Alt text / description',
                    'instructions' => 'Image alt text, or the video\'s accessible label — also used as the lightbox heading.',
                    'required' => 1,
                ])
                ->addSelect('column_span', [
                    'label' => 'Width',
                    'choices' => [
                        1 => '1 column (square)',
                        2 => '2 columns (wide)',
                    ],
                    'default_value' => 1,
                    'ui' => true,
                ])
            ->endRepeater();

        return $fields->build();
    }

    /**
     * Retrieve the raw repeater rows.
     *
     * @return array
     */
    public function items()
    {
        return get_field('items') ?: $this->example['items'];
    }

    /**
     * Normalize every item to a shape the view doesn't need to guard
     * (consistent thumbnail/video-url/column-span keys regardless of
     * media type), then group into carousel-page chunks.
     *
     * @return array
     */
    public function pages()
    {
        $items = array_map(fn($item) => $this->normalizeItem($item), $this->items());

        return $this->paginate($items);
    }

    /**
     * Normalize a single repeater row.
     *
     * @param  array  $item
     * @return array
     */
    protected function normalizeItem(array $item)
    {
        $isVideo = ($item['media_type'] ?? 'image') === 'video';
        $columnSpan = (int) ($item['column_span'] ?? 1) === 2 ? 2 : 1;

        if ($isVideo) {
            $thumbnail = is_array($item['video_poster'] ?? null) ? $item['video_poster'] : null;
            $video = is_array($item['video'] ?? null) ? $item['video'] : null;
        } else {
            $thumbnail = is_array($item['image'] ?? null) ? $item['image'] : null;
            $video = null;
        }

        return [
            'type' => $isVideo ? 'video' : 'image',
            'thumbnail' => $thumbnail,
            'videoUrl' => $video['url'] ?? null,
            'alt' => $item['alt'] ?? '',
            'columnSpan' => $columnSpan,
        ];
    }

    /**
     * Group normalized items into pages, each capped at PAGE_CAPACITY
     * column-units (4 columns × 3 rows) — a greedy bin-fill using
     * columnSpan as the per-item unit cost.
     *
     * @param  array  $items
     * @return array
     */
    protected function paginate(array $items)
    {
        $pages = [];
        $page = [];
        $used = 0;

        foreach ($items as $item) {
            if ($used + $item['columnSpan'] > self::PAGE_CAPACITY && $page) {
                $pages[] = $page;
                $page = [];
                $used = 0;
            }

            $page[] = $item;
            $used += $item['columnSpan'];
        }

        if ($page) {
            $pages[] = $page;
        }

        return $pages;
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
