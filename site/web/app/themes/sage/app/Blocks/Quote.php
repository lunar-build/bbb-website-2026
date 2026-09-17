<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class Quote extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Quote';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'quote';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A pull quote with optional attribution name and role.';

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
    public $icon = 'format-quote';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'quote',
        'pull quote',
        'testimonial',
        'attribution',
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
     * Figma showed "Short" and "Long" variants — both have attribution,
     * the difference is quote *length* (a punchy one-liner at a larger
     * type size vs. a multi-paragraph quote), not presence/absence of
     * attribution. Merged into a single block (no variant field) since
     * that's purely a content-length difference, not a structural one —
     * attribution is simply optional and hides when blank. $examples
     * below shows both on the pattern library page for awareness (see
     * build-acf-block skill §6 and PatternLibrary::render()'s `_content`
     * handling for InnerBlocks-based variant content).
     *
     * @var array
     */
    public $example = [
        'quote_size' => 'large',
        'attribution_name' => 'Priya Chandra',
        'attribution_role' => 'Cabinet Member for Sustainable Transport Delivery',
    ];

    /**
     * @var array
     */
    public $examples = [
        'Short' => [
            'quote_size' => 'large',
            'attribution_name' => 'Ben',
            'attribution_role' => 'Cyclists in Bristol',
            '_content' => '<p>Find the right cycling group for you — there\'s plenty of choice!</p>',
        ],
        'Long' => [
            'quote_size' => 'standard',
            'attribution_name' => 'Councillor Lucy Hodge',
            'attribution_role' => 'Cabinet Member for Sustainable Transport Delivery',
            '_content' => '<p>We want to make it safer, easier and more pleasant for people to get around, whether they are walking, wheeling, cycling or using the bus and these schemes will help achieve that. They will improve everyday connections and, at Bear Flat, help buses run more reliably.</p>'
                .'<p>In Royal Victoria Park, we have listened to feedback and refined the plans, retaining vehicle access while delivering better facilities for people walking, wheeling and cycling through the park. The improvements will create safer crossings, better accessibility and a more welcoming environment for everyone.</p>'
                .'<p>We will do our best to reduce disruption to traffic while these are installed and want to thank people for their patience as these improvements are put in.</p>',
        ],
    ];

    /**
     * The block template.
     *
     * @var array
     */
    public $template = [
        'core/paragraph' => ['placeholder' => 'Quote text…'],
    ];

    /**
     * Default fixture markup standing in for this block's InnerBlocks
     * content — used for the block editor's own empty-state preview, and
     * as the pattern-library fallback for any block without $examples set.
     * On the pattern library page specifically, $examples above (via its
     * per-variant `_content` key) overrides this per variant instead.
     *
     * @var string
     */
    public $exampleContent = '<p>Investing in safer streets and better cycle routes isn\'t just good for the environment — it\'s good for our high streets, our health, and our children\'s future.</p>';

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'quoteSize' => $this->quoteSize(),
            'attributionName' => $this->attributionName(),
            'attributionRole' => $this->attributionRole(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('quote');

        $fields
            ->addSelect('quote_size', [
                'label' => 'Quote size',
                'instructions' => 'Matches the Figma "Short"/"Long" variants — Large suits a punchy one-liner, Standard suits a longer, multi-paragraph quote.',
                'choices' => [
                    'large' => 'Large (short quote)',
                    'standard' => 'Standard (long quote)',
                ],
                'default_value' => 'large',
                'required' => 1,
            ])
            ->addText('attribution_name', [
                'label' => 'Attribution name',
                'required' => 0,
            ])
            ->addText('attribution_role', [
                'label' => 'Attribution role',
                'required' => 0,
                'instructions' => 'e.g. "Cabinet Member for Sustainable Transport Delivery". Leave blank to hide.',
            ]);

        return $fields->build();
    }

    /**
     * Retrieve the quote size ('large' or 'standard').
     *
     * @return string
     */
    public function quoteSize()
    {
        return get_field('quote_size') ?: $this->example['quote_size'];
    }

    /**
     * Retrieve the attribution name.
     *
     * @return string
     */
    public function attributionName()
    {
        return get_field('attribution_name') ?: $this->example['attribution_name'];
    }

    /**
     * Retrieve the attribution role.
     *
     * @return string
     */
    public function attributionRole()
    {
        return get_field('attribution_role') ?: $this->example['attribution_role'];
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
