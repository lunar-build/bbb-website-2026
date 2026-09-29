<?php

namespace App\Blocks;

use Illuminate\Support\Facades\Vite;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class RollingStatsCarousel extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Rolling Stats Carousel';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'rolling-stats-carousel';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A carousel of rotating big-number stats (e.g. "7.5 miles of cycling commuter routes in Bristol"), each with a supporting illustration and caption. Numbers count up when their slide becomes active.';

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
    public $icon = 'chart-bar';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'stats',
        'carousel',
        'numbers',
        'counter',
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
        'slides' => [
            [
                'prefix_text' => 'There are',
                'value' => 7.5,
                'unit' => 'miles',
                'caption_text' => 'of cycling commuter routes in Bristol',
            ],
            [
                'prefix_text' => 'Over the last year',
                'value' => 1200,
                'unit' => 'bikes',
                'caption_text' => 'were loaned out for free across the West of England',
            ],
            [
                'prefix_text' => 'We\'ve helped train',
                'value' => 4.2,
                'unit' => 'thousand people',
                'caption_text' => 'to ride with confidence since the scheme began',
            ],
        ],
    ];

    /**
     * Fallback example data requiring a non-constant expression (Vite::asset).
     *
     * @return array
     */
    public function example(): array
    {
        $placeholder = ['url' => Vite::asset('resources/images/placeholder/pattern-placeholder.svg'), 'alt' => ''];

        $slides = $this->example['slides'];

        foreach ($slides as &$slide) {
            $slide['image'] = $placeholder;
        }

        return ['slides' => $slides];
    }

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'slides' => $this->slides(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('rolling_stats_carousel');

        $slides = $fields->addRepeater('slides', [
            'label' => 'Slides',
            'button_label' => 'Add stat',
            'min' => 1,
            'layout' => 'block',
        ]);

        $slides
            ->addImage('image', [
                'label' => 'Illustration',
                'instructions' => 'Supporting illustration shown alongside this stat (e.g. a helmet or pump graphic).',
                'return_format' => 'array',
                'preview_size' => 'medium',
                'required' => 0,
            ])
            ->addText('prefix_text', [
                'label' => 'Intro text',
                'instructions' => 'Shown above the number, e.g. "There are".',
                'default_value' => 'There are',
            ])
            ->addNumber('value', [
                'label' => 'Stat value',
                'instructions' => 'The number this stat counts up to (e.g. 7.5). Decimals are supported.',
                'required' => 1,
            ])
            ->addText('unit', [
                'label' => 'Unit / suffix',
                'instructions' => 'Shown immediately after the number, e.g. "miles".',
            ])
            ->addTextarea('caption_text', [
                'label' => 'Caption',
                'instructions' => 'Shown below the number, e.g. "of cycling commuter routes in Bristol".',
                'rows' => 2,
                'required' => 1,
            ]);

        $slides->endRepeater();

        return $fields->build();
    }

    /**
     * Retrieve the slides, normalized to a safe shape with a real
     * placeholder image and an accessible full-sentence fallback for
     * screen readers (the number itself is animated presentationally).
     *
     * @return array
     */
    public function slides()
    {
        $placeholder = Vite::asset('resources/images/placeholder/pattern-placeholder.svg');

        $slides = get_field('slides') ?: ($this->example['slides'] ?? []);

        return array_map(function ($slide) use ($placeholder) {
            $image = is_array($slide['image'] ?? null) ? $slide['image'] : [];

            if (empty($image['url'])) {
                $image = ['url' => $placeholder, 'alt' => $image['alt'] ?? ''];
            }

            $value = (float) ($slide['value'] ?? 0);
            $decimals = strlen(substr(strrchr((string) $value, '.'), 1) ?: '');
            $prefix = $slide['prefix_text'] ?? '';
            $unit = $slide['unit'] ?? '';
            $caption = $slide['caption_text'] ?? '';

            // No thousands separator — matches the plain toFixed() output the
            // JS count-up renders visually, so the sr-only sentence never
            // disagrees with the number left on screen once it finishes.
            $formattedValue = number_format($value, $decimals, '.', '');

            $accessibleText = trim(sprintf('%s %s%s %s', $prefix, $formattedValue, $unit ? ' '.$unit : '', $caption));

            return [
                'image' => $image,
                'prefix' => $prefix,
                'value' => $value,
                'decimals' => $decimals,
                'unit' => $unit,
                'caption' => $caption,
                'accessibleText' => $accessibleText,
            ];
        }, $slides);
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
