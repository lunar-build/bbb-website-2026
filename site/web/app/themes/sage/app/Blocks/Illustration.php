<?php

namespace App\Blocks;

use App\Blocks\Concerns\ResolvesFields;
use Illuminate\Support\Facades\Vite;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class Illustration extends Block
{
    use ResolvesFields;

    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Illustration';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'illustration';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A decorative Better by Bike illustration, picked from the library.';

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
    public $icon = 'format-image';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'illustration',
        'artwork',
        'decorative',
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
            'top' => 'var:preset|spacing|small',
            'bottom' => 'var:preset|spacing|small',
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
     * The illustrations bundled in resources/images/illustrations, keyed by file name
     * (underscores for hyphens).
     *
     * @var array
     */
    public const ILLUSTRATIONS = [
        'cyclist' => 'Cyclist on bike',
        'grandma' => 'Older cyclist',
        'mountain_biker' => 'Mountain biker',
        'bmx' => 'BMX rider',
        'instructor' => 'Cycle instructor',
        'helmet' => 'Helmet',
        'pump' => 'Bike pump',
    ];

    /**
     * The block preview example data.
     *
     * @var array
     */
    public $example = [
        'illustration' => 'cyclist',
        'position' => 'centre',
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        $key = $this->resolveField('illustration', '');

        return [
            'url' => isset(self::ILLUSTRATIONS[$key]) ? Vite::asset('resources/images/illustrations/' . str_replace('_', '-', $key) . '.png') : null,
            'position' => $this->resolveField('position', 'centre'),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('illustration');

        $fields
            ->addSelect('illustration', [
                'label' => 'Illustration',
                'instructions' => 'Decorative artwork, hidden from screen readers.',
                'choices' => self::ILLUSTRATIONS,
                'default_value' => 'cyclist',
                'ui' => true,
                'required' => true,
            ])
            ->addButtonGroup('position', [
                'label' => 'Position',
                'choices' => [
                    'left' => 'Left',
                    'centre' => 'Centre',
                    'right' => 'Right',
                ],
                'default_value' => 'centre',
            ]);

        return $fields->build();
    }
}
