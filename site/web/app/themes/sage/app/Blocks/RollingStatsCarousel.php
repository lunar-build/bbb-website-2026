<?php

namespace App\Blocks;

use App\Blocks\Concerns\HasIllustrations;
use App\Blocks\Concerns\ResolvesFields;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class RollingStatsCarousel extends Block
{
    use HasIllustrations;
    use ResolvesFields;

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
    public $description = 'A carousel of rotating big-number stats (e.g. "7.5 miles of cycling commuter routes in Bristol"), flanked by optional left/right illustrations. Numbers spin up digit-by-digit, slot-counter style, when their slide becomes active.';

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
        'image_left' => 'pump',
        'image_right' => 'helmet',
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
                'link' => ['title' => 'Find out more', 'url' => '#', 'target' => ''],
            ],
            [
                'prefix_text' => 'We\'ve helped train',
                'value' => 4.2,
                'unit' => 'thousand people',
                'caption_text' => 'to ride with confidence since the scheme began',
            ],
            [
                'slide_type' => 'statement',
                'statement' => '<strong>28%</strong> of Bristol residents cycle at least once a week, <strong>23%</strong> don’t cycle but would like to',
                'source' => 'BikeLife Bristol 2019',
            ],
            [
                'slide_type' => 'statement',
                'statement' => 'Cycle training can significantly improve confidence, as well as safety.',
            ],
        ],
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'imageLeft' => $this->imageLeft(),
            'imageRight' => $this->imageRight(),
            'slides' => $this->slides(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('rolling_stats_carousel');

        $fields
            ->addSelect('image_left', [
                'label' => 'Left illustration',
                'instructions' => $this->illustrationInstructions(),
                'choices' => $this->illustrations(),
                'default_value' => '',
                'ui' => true,
                'allow_null' => true,
            ])
            ->addSelect('image_right', [
                'label' => 'Right illustration',
                'instructions' => $this->illustrationInstructions(),
                'choices' => $this->illustrations(),
                'default_value' => '',
                'ui' => true,
                'allow_null' => true,
            ]);

        $slides = $fields->addRepeater('slides', [
            'label' => 'Slides',
            'button_label' => 'Add stat',
            'min' => 1,
            'layout' => 'block',
        ]);

        $stat = [[['field' => 'slide_type', 'operator' => '==', 'value' => 'stat']]];
        $statement = [[['field' => 'slide_type', 'operator' => '==', 'value' => 'statement']]];

        $slides
            ->addSelect('slide_type', [
                'label' => 'Slide type',
                'instructions' => 'Stat: a number that counts up. Statement: a line of text, with optional bold figures.',
                'choices' => [
                    'stat' => 'Stat (counting number)',
                    'statement' => 'Statement (text only)',
                ],
                'default_value' => 'stat',
                'ui' => true,
            ])
            ->addText('prefix_text', [
                'label' => 'Intro text',
                'instructions' => 'Shown above the number, e.g. "There are". Optional.',
                'conditional_logic' => $stat,
            ])
            ->addNumber('value', [
                'label' => 'Stat value',
                'instructions' => 'The number this stat counts up to (e.g. 7.5). Decimals are supported.',
                'required' => 1,
                'conditional_logic' => $stat,
            ])
            ->addText('unit', [
                'label' => 'Unit / suffix',
                'instructions' => 'Shown immediately after the number, e.g. "miles". Optional.',
                'conditional_logic' => $stat,
            ])
            ->addTextarea('caption_text', [
                'label' => 'Caption',
                'instructions' => 'Shown below the number, e.g. "of cycling commuter routes in Bristol". Optional.',
                'rows' => 2,
                'conditional_logic' => $stat,
            ])
            ->addWysiwyg('statement', [
                'label' => 'Statement',
                'instructions' => 'Select a figure and use Bold to make it stand out.',
                'tabs' => 'visual',
                'toolbar' => 'basic',
                'media_upload' => 0,
                'required' => 1,
                'conditional_logic' => $statement,
            ])
            ->addText('source', [
                'label' => 'Source',
                'instructions' => 'e.g. "BikeLife Bristol 2019". Optional.',
            ])
            ->addLink('link', [
                'label' => 'Button',
                'instructions' => 'Optional. Shown as a button under the slide, e.g. "Find out more".',
            ]);

        $slides->endRepeater();

        return $fields->build();
    }

    /**
     * Retrieve the slides, normalized to a safe shape with an accessible
     * full-sentence fallback for screen readers (the number itself is
     * animated presentationally) and a pre-split 'digits' array driving the
     * slot-counter reels — each entry is either a single digit character or
     * '.', built server-side so the front end never has to re-derive it.
     *
     * @return array
     */
    public function slides()
    {
        $slides = $this->resolveField('slides', []);

        // The repeater's min of 1 makes ACF add a blank row on insert; drop
        // rows with nothing to show so the block still counts as empty.
        $slides = array_filter($slides, fn($slide) => ($slide['slide_type'] ?? 'stat') === 'statement'
            ? trim(wp_strip_all_tags($slide['statement'] ?? '')) !== ''
            : ($slide['value'] ?? '') !== '');

        return array_map(function ($slide) {
            $source = trim($slide['source'] ?? '');
            $link = normalize_link($slide['link'] ?? null);

            if (($slide['slide_type'] ?? 'stat') === 'statement') {
                $statement = wp_kses($slide['statement'] ?? '', ['strong' => [], 'b' => [], 'em' => [], 'br' => []]);

                return [
                    'type' => 'statement',
                    'statement' => trim(preg_replace('#</?p>#i', '', $statement)),
                    'source' => $source,
                    'link' => $link,
                ];
            }

            $value = (float) ($slide['value'] ?? 0);
            $decimals = strlen(substr(strrchr((string) $value, '.'), 1) ?: '');
            $prefix = $slide['prefix_text'] ?? '';
            $unit = $slide['unit'] ?? '';
            $caption = $slide['caption_text'] ?? '';

            // No thousands separator — matches the plain toFixed() output the
            // JS slot counter renders visually, so the sr-only sentence never
            // disagrees with the number left on screen once it finishes.
            $formattedValue = number_format($value, $decimals, '.', '');

            $accessibleText = trim(sprintf('%s %s%s %s', $prefix, $formattedValue, $unit ? ' ' . $unit : '', $caption));

            return [
                'type' => 'stat',
                'prefix' => $prefix,
                'value' => $value,
                'decimals' => $decimals,
                'formattedValue' => $formattedValue,
                'digits' => str_split($formattedValue),
                'unit' => $unit,
                'caption' => $caption,
                'accessibleText' => $accessibleText,
                'source' => $source,
                'link' => $link,
            ];
        }, $slides);
    }

    /**
     * Retrieve the left illustration URL.
     *
     * @return string|null
     */
    public function imageLeft()
    {
        return $this->illustrationUrl($this->resolveField('image_left'));
    }

    /**
     * Retrieve the right illustration URL.
     *
     * @return string|null
     */
    public function imageRight()
    {
        return $this->illustrationUrl($this->resolveField('image_right'));
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
