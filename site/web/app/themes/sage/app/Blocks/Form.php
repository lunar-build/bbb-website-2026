<?php

namespace App\Blocks;

use App\Blocks\Concerns\HasIllustrations;
use App\Blocks\Concerns\ResolvesFields;
use App\Fields\Copy;
use App\Fields\Heading;
use Illuminate\Support\Facades\Vite;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class Form extends Block
{
    use HasIllustrations;
    use ResolvesFields;

    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Form';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'form';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'An intro heading/text with a Gravity Forms form, either alongside an image or centred with no image.';

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
    public $icon = 'feedback';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'form',
        'gravity forms',
        'gravityforms',
        'enquiry',
        'contact',
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
            'background' => true,
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
     * Image crop positions (select value => label); the value, with `_` as a
     * space, is the CSS object-position.
     *
     * @var array
     */
    public $imagePositions = [
        'center' => 'Center',
        'center_top' => 'Top',
        'center_bottom' => 'Bottom',
        'left_center' => 'Left',
        'right_center' => 'Right',
    ];

    /**
     * The block preview example data.
     *
     * @var array
     */
    public $example = [
        'layout' => 'image',
        'form_id' => 1,
        'heading_highlight' => 'Stay up to date',
        'heading_text' => 'with the latest news and events in your region',
        'heading_level' => 'h2',
        'heading_style' => 'match',
        'subheading_text' => 'Subtitle…',
        'subheading_level' => 'h3',
        'subheading_style' => 'match',
        'intro_text' => 'A short paragraph of intro copy above the form.',
        'intro_style' => 'body',
    ];

    /**
     * Component combinations to render stacked on the pattern-library page
     * (see App\View\Composers\PatternLibrary::render()) — each entry is
     * merged onto $example above, so only needs to override what differs.
     *
     * @var array
     */
    public $examples = [
        'Image' => [],
        'Centred' => [
            'layout' => 'centred',
            'heading_text' => 'If you have any questions or would like some advice, please feel free to get in touch.',
        ],
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'layout' => $this->layout(),
            'formId' => $this->formId(),
            'headingHighlight' => $this->headingHighlight(),
            'heading' => $this->heading(),
            'subheading' => $this->subheading(),
            'intro' => $this->intro(),
            'imageType' => $this->resolveField('image_type', 'photo'),
            'image' => $this->image(),
            'imagePosition' => $this->imagePosition(),
            'illustration' => $this->illustration(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('form');

        $fields->addTab('Content');

        $fields->addSelect('layout', [
            'label' => 'Layout',
            'instructions' => 'Image: form alongside an image. Centred: form only, no image. The heading and text below show beside/above the form in both.',
            'choices' => [
                'image' => 'Image with form',
                'centred' => 'Centred (no image)',
            ],
            'default_value' => 'image',
            'ui' => true,
        ]);

        $fields->addText('heading_highlight', [
            'label' => 'Heading (highlighted words)',
            'instructions' => 'Optional bold lead-in shown before the heading, e.g. "Stay up to date". The rest of the heading then renders in a lighter weight.',
        ]);

        $fields->addPartial(Heading::class, [
            'name' => 'heading',
            'label' => 'Heading',
            'default_level' => 'h2',
            'required' => true,
        ]);

        $fields->addPartial(Heading::class, [
            'name' => 'subheading',
            'label' => 'Subheading',
            'default_level' => 'h3',
        ]);

        $fields->addPartial(Copy::class, [
            'name' => 'intro',
            'label' => 'Intro text',
            'default_style' => 'body',
        ]);

        // Tab-level condition: the Image tab only exists for the image layout.
        $fields->addTab('Image', [
            'conditional_logic' => [
                [
                    [
                        'field' => 'layout',
                        'operator' => '==',
                        'value' => 'image',
                    ],
                ],
            ],
        ]);

        $fields->addButtonGroup('image_type', [
            'label' => 'Picture',
            'instructions' => 'A photo, or one of the illustrations. The block\'s background colour (set in the sidebar) sits behind either.',
            'choices' => [
                'photo' => 'Photo',
                'illustration' => 'Illustration',
            ],
            'default_value' => 'photo',
        ]);

        $fields->addImage('image', [
            'label' => 'Image',
            'instructions' => 'Shown alongside the form.',
            'return_format' => 'array',
            'preview_size' => 'medium',
            'required' => 1,
            'conditional_logic' => [[['field' => 'image_type', 'operator' => '==', 'value' => 'photo']]],
        ]);

        $fields->addSelect('image_position', [
            'label' => 'Image position',
            'instructions' => 'Which part of the image stays visible when it\'s cropped to fit.',
            'choices' => $this->imagePositions,
            'default_value' => 'center',
            'ui' => true,
            'conditional_logic' => [[['field' => 'image_type', 'operator' => '==', 'value' => 'photo']]],
        ]);

        $fields->addSelect('illustration', [
            'label' => 'Illustration',
            'instructions' => $this->illustrationInstructions(),
            'choices' => $this->illustrations(),
            'default_value' => 'mountain_biker',
            'ui' => true,
            'conditional_logic' => [[['field' => 'image_type', 'operator' => '==', 'value' => 'illustration']]],
        ]);

        $fields->addTab('Form');

        $fields
            ->addSelect('form_id', [
                'label' => 'Choose a Gravity Form',
                'instructions' => 'Select which Gravity Forms form to display.',
                'choices' => $this->formChoices(),
                'ui' => 1,
                'allow_null' => 1,
                'required' => 1,
            ]);

        return $fields->build();
    }

    /**
     * Retrieve the available Gravity Forms forms as select choices.
     *
     * @return array
     */
    protected function formChoices()
    {
        if (! class_exists('GFAPI')) {
            // Don't crash WP-CLI: it bootstraps this same block-registration hook,
            // so a hard throw here would block `wp plugin activate` itself — the
            // one command that fixes this.
            if (defined('WP_CLI') && \WP_CLI) {
                return [];
            }

            throw new \Exception('Gravity Forms is not installed or activated. The Form block will not work without it.');
        }

        return collect(\GFAPI::get_forms())->pluck('title', 'id')->all();
    }

    /**
     * Retrieve the selected layout.
     *
     * @return string
     */
    public function layout()
    {
        return $this->resolveField('layout', 'image');
    }

    /**
     * Retrieve the image's crop position as a CSS object-position value.
     *
     * @return string
     */
    public function imagePosition()
    {
        $position = get_field('image_position');

        return isset($this->imagePositions[$position]) ? str_replace('_', ' ', $position) : 'center';
    }

    /**
     * Retrieve the chosen illustration's URL, or null.
     *
     * @return string|null
     */
    public function illustration()
    {
        return $this->illustrationUrl($this->resolveField('illustration', ''));
    }

    /**
     * Retrieve the image, falling back to a placeholder.
     *
     * @return array
     */
    public function image()
    {
        return get_field('image') ?: [
            'url' => Vite::asset('resources/images/placeholder/pattern-placeholder.svg'),
            'alt' => '',
        ];
    }

    /**
     * Retrieve the optional bold lead-in words for the heading.
     *
     * @return string
     */
    public function headingHighlight()
    {
        return $this->resolveField('heading_highlight', '');
    }

    /**
     * Retrieve the heading text/level/style.
     *
     * @return array
     */
    public function heading()
    {
        return [
            'text' => $this->resolveField('heading_text', ''),
            'level' => $this->resolveField('heading_level', 'h2'),
            'style' => $this->resolveField('heading_style', 'match'),
        ];
    }

    /**
     * Retrieve the subheading text/level/style. Empty text is valid — the
     * subheading is optional.
     *
     * @return array
     */
    public function subheading()
    {
        return [
            'text' => $this->resolveField('subheading_text', ''),
            'level' => $this->resolveField('subheading_level', 'h3'),
            'style' => $this->resolveField('subheading_style', 'match'),
        ];
    }

    /**
     * Retrieve the intro text/style. Empty text is valid — the intro
     * paragraph is optional.
     *
     * @return array
     */
    public function intro()
    {
        // get_field() already applies the 'intro_text' field's
        // new_lines => 'wpautop' (see Fields\Copy) — the example fallback
        // bypasses ACF entirely, so it needs the same wpautop() pass
        // manually to match (real <p> tags for the preview/example).
        $text = get_field('intro_text');

        return [
            'text' => $this->resolveField('intro_text', ''),
            'style' => $this->resolveField('intro_style', 'body'),
        ];
    }

    /**
     * Retrieve the selected Gravity Forms form ID.
     *
     * @return int
     */
    public function formId()
    {
        return $this->resolveField('form_id');
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
