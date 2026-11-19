<?php

namespace App\Blocks;

use App\Blocks\Concerns\ResolvesFields;
use Illuminate\Support\Facades\Vite;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class TypesOfBikes extends Block
{
    use ResolvesFields;

    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Types of Bikes';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'types-of-bikes';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A row of bike cards, each with an illustration, description and an "Includes" list of icons.';

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
    public $icon = 'screenoptions';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'bike',
        'types',
        'cards',
        'includes',
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
        'anchor' => true,
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
     * Bike illustrations an editor can pick from (slug => label). The files
     * live in resources/images/bikes/{slug}.svg.
     *
     * @var array
     */
    protected $bikeChoices = [
        'regular-bike' => 'Regular bike',
        'folding-bike' => 'Folding bike',
        'electric-bike' => 'Electric bike',
        'converted-e-bike' => 'Converted e-bike',
    ];

    /**
     * "Includes" icons an editor can pick from (slug => label). The files
     * live in resources/images/icons/bike-includes/{slug}.svg.
     *
     * @var array
     */
    protected $includeChoices = [
        'lock' => 'Lock',
        'storage' => 'Storage',
        'lights' => 'Lights',
        'bell' => 'Bell',
        'battery-charger' => 'Battery charger',
    ];

    /**
     * The block preview example data.
     *
     * @var array
     */
    public $example = [
        'bikes' => [
            [
                'illustration' => 'regular-bike',
                'title' => 'Regular bike',
                'description' => 'Suitable for all road conditions. We offer both regular and low-step frames. Bikes are regularly serviced and come with panniers for storage, bike lock and lights.',
                'includes' => [
                    ['icon' => 'lock', 'label' => 'Lock'],
                    ['icon' => 'storage', 'label' => 'Storage'],
                    ['icon' => 'lights', 'label' => 'Lights'],
                    ['icon' => 'bell', 'label' => 'Bell'],
                ],
            ],
            [
                'illustration' => 'folding-bike',
                'title' => 'Folding bike',
                'description' => 'Suitable for people with limited storage at home, work and for commuters taking onboard a train or bus. The bike easily folds down into a compact size for easy carrying. Bikes are regularly serviced and come with lock and lights.',
                'includes' => [
                    ['icon' => 'lock', 'label' => 'Lock'],
                    ['icon' => 'storage', 'label' => 'Storage'],
                    ['icon' => 'lights', 'label' => 'Lights'],
                    ['icon' => 'bell', 'label' => 'Bell'],
                ],
            ],
            [
                'illustration' => 'electric-bike',
                'title' => 'Electric bike',
                'description' => 'Suitable for people who want extra assistance for travelling further distances and for getting up steep hills. Bikes are regularly serviced and come with panniers for storage, bike lock and lights.',
                'includes' => [
                    ['icon' => 'lock', 'label' => 'Lock'],
                    ['icon' => 'storage', 'label' => 'Storage'],
                    ['icon' => 'lights', 'label' => 'Lights'],
                    ['icon' => 'bell', 'label' => 'Bell'],
                    ['icon' => 'battery-charger', 'label' => 'Battery charger'],
                ],
            ],
            [
                'illustration' => 'converted-e-bike',
                'title' => 'Converted e-bike',
                'description' => 'Need some extra oomph to travel further, or ride up steep hills? Our converted e-bikes are regularly serviced and come with panniers for storage, a bike lock and lights. Note: The e-bike conversion kit is pre-installed on our regular bike.',
                'includes' => [
                    ['icon' => 'lock', 'label' => 'Lock'],
                    ['icon' => 'storage', 'label' => 'Storage'],
                    ['icon' => 'lights', 'label' => 'Lights'],
                    ['icon' => 'bell', 'label' => 'Bell'],
                    ['icon' => 'battery-charger', 'label' => 'Battery charger'],
                ],
            ],
        ],
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'bikes' => $this->bikes(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('types_of_bikes');

        $bikes = $fields->addRepeater('bikes', [
            'label' => 'Bikes',
            'button_label' => 'Add bike',
            'min' => 0,
            'layout' => 'block',
        ]);

        $bikes
            ->addSelect('illustration', [
                'label' => 'Illustration',
                'choices' => $this->bikeChoices,
                'default_value' => 'regular-bike',
                'ui' => true,
            ])
            ->addText('title', [
                'label' => 'Title',
                'required' => 1,
            ])
            ->addTextarea('description', [
                'label' => 'Description',
                'rows' => 4,
            ])
            ->addRepeater('includes', [
                'label' => 'Includes',
                'instructions' => 'Shown as an icon grid under "Includes:". Leave empty to hide the section.',
                'button_label' => 'Add item',
                'min' => 0,
                'layout' => 'table',
            ])
                ->addSelect('icon', [
                    'label' => 'Icon',
                    'choices' => $this->includeChoices,
                    'default_value' => 'lock',
                    'ui' => true,
                ])
                ->addText('label', [
                    'label' => 'Label',
                    'instructions' => 'Defaults to the icon name.',
                ])
            ->endRepeater();

        $bikes->endRepeater();

        return $fields->build();
    }

    /**
     * Retrieve the bikes with illustration/icon slugs resolved to asset
     * URLs, dropping any slug that no longer matches a bundled file.
     *
     * @return array
     */
    public function bikes()
    {
        $bikes = $this->resolveField('bikes', []);

        return array_map(function ($bike) {
            $illustration = $bike['illustration'] ?? '';

            $includes = array_map(function ($include) {
                $icon = $include['icon'] ?? '';

                return [
                    'src' => Vite::asset("resources/images/icons/bike-includes/{$icon}.svg"),
                    'label' => ($include['label'] ?? '') ?: ($this->includeChoices[$icon] ?? ''),
                ];
            }, array_filter($bike['includes'] ?? [], fn ($include) => isset($this->includeChoices[$include['icon'] ?? ''])));

            return [
                'illustration' => isset($this->bikeChoices[$illustration])
                    ? Vite::asset("resources/images/bikes/{$illustration}.svg")
                    : null,
                'title' => $bike['title'] ?? '',
                'description' => $bike['description'] ?? '',
                'includes' => array_values($includes),
            ];
        }, $bikes);
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
