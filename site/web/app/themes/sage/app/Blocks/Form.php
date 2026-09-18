<?php

namespace App\Blocks;

use App\Fields\Copy;
use App\Fields\Heading;
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
        'heading_text' => 'Get in touch',
        'heading_level' => 'h2',
        'heading_style' => 'match',
        'subheading_text' => 'Subtitle…',
        'subheading_level' => 'h3',
        'subheading_style' => 'match',
        'intro_text' => 'A short paragraph of intro copy above the form.',
        'intro_style' => 'body',
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'formId' => $this->formId(),
            'heading' => $this->heading(),
            'subheading' => $this->subheading(),
            'intro' => $this->intro(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('form');

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
     * Retrieve the heading text/level/style.
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
     * Retrieve the subheading text/level/style. Empty text is valid — the
     * subheading is optional.
     *
     * @return array
     */
    public function subheading()
    {
        return [
            'text' => get_field('subheading_text') ?: ($this->example['subheading_text'] ?? ''),
            'level' => get_field('subheading_level') ?: ($this->example['subheading_level'] ?? 'h3'),
            'style' => get_field('subheading_style') ?: ($this->example['subheading_style'] ?? 'match'),
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
        return [
            'text' => get_field('intro_text') ?: ($this->example['intro_text'] ?? ''),
            'style' => get_field('intro_style') ?: ($this->example['intro_style'] ?? 'body'),
        ];
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
