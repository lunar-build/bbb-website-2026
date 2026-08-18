<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class Form extends Block
{
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
    public $description = 'An intro heading/text with a Gravity Forms form.';

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
    public $mode = 'preview';

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
        'multiple' => true,
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
     * The block preview example data.
     *
     * @var array
     */
    public $example = [
        'form_id' => 1,
    ];

    /**
     * The block template.
     *
     * @var array
     */
    public $template = [
        ['core/heading' => ['placeholder' => 'Get in touch', 'level' => 2]],
        ['core/heading' => ['placeholder' => 'Subtitle…', 'level' => 3]],
        ['core/paragraph' => ['placeholder' => 'A short paragraph of intro copy above the form.']],
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'formId' => $this->formId(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('form');

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
              error_log('Gravity Forms is not installed or activated. The Form block will not work without it.');

            add_action('admin_notices', fn () => printf(
                '<div class="notice notice-error"><p>%s</p></div>',
                esc_html__('Gravity Forms is not installed or activated. The Form block will not work without it.', 'sage')
            ));
            return [];
        }

        return collect(\GFAPI::get_forms())->pluck('title', 'id')->all();
    }

    /**
     * Retrieve the selected Gravity Forms form ID.
     *
     * @return int
     */
    public function formId()
    {
        return get_field('form_id') ?: $this->example['form_id'];
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
