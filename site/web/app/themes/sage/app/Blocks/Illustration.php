<?php

namespace App\Blocks;

use App\Blocks\Concerns\HasIllustrations;
use App\Blocks\Concerns\ResolvesFields;
use App\Support\Illustrations;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class Illustration extends Block
{
    use HasIllustrations;
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
    public $description = 'A decorative Better by Bike character with up to three objects around it, on an optional blue strip.';

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
        'character',
        'artwork',
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
            'bottom' => '0',
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
        'character' => 'mountain_biker',
        'character_2' => '',
        'object_1' => 'pedal',
        'object_2' => 'helmet',
        'object_3' => '',
        'remove_blue_background' => false,
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'character' => $this->illustrationUrl($this->resolveField('character', '')),
            'character2' => $this->illustrationUrl($this->resolveField('character_2', '')),
            'objects' => array_values(array_filter(array_map(
                fn($n) => $this->illustrationUrl($this->resolveField("object_$n", '')),
                [1, 2, 3],
            ))),
            'removeBackground' => $this->resolveBoolField('remove_blue_background'),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('illustration');
        $characters = Illustrations::characters();

        $fields->addSelect('character', [
            'label' => 'Character',
            'choices' => $characters,
            'default_value' => 'mountain_biker',
            'ui' => true,
            'required' => true,
        ]);

        $fields->addSelect('character_2', [
            'label' => 'Second character',
            'instructions' => 'Optional. Shown beside the first from tablet width up.',
            'choices' => $characters,
            'ui' => true,
            'allow_null' => true,
        ]);

        foreach ([1, 2, 3] as $n) {
            $fields->addSelect("object_$n", [
                'label' => "Optional object $n",
                'choices' => Illustrations::objects(),
                'ui' => true,
                'allow_null' => true,
                'wrapper' => ['width' => 33],
            ]);
        }

        $fields->addTrueFalse('remove_blue_background', [
            'label' => 'Remove blue background',
            'instructions' => 'This block has a partial blue background strip at the base of the character, which suits a following block that also has a blue background. If the next block has no background, removing it may look better.',
            'ui' => true,
        ]);

        return $fields->build();
    }
}
