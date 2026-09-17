<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class Quote extends Block
{
    public $name = 'Quote';

    public $slug = 'quote';

    public $description = 'A pull quote with optional attribution name and role.';

    public $category = 'text';

    public $icon = 'format-quote';

    public $keywords = [
        'quote',
        'pull quote',
        'testimonial',
        'attribution',
    ];

    public $post_types = ['post', 'page'];

    public $parent = [];

    public $ancestor = [];

    public $mode = 'auto';

    public $align = '';

    public $align_text = '';

    public $align_content = '';

    public $spacing = [
        'padding' => null,
        'margin' => null,
    ];

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

    public $styles = [];

    /**
     * Figma showed "Short" and "Long" variants — both have attribution,
     * the difference is quote *length* (a punchy one-liner at a larger
     * type size vs. a multi-paragraph quote), not presence/absence of
     * attribution. Merged into a single block (no variant field) since
     * that's purely a content-length difference, not a structural one —
     * attribution is simply optional and hides when blank. $examples
     * below shows both on the pattern library page for awareness (see
     * build-acf-block skill §6).
     */
    public $example = [
        'quote_size' => 'large',
        'quote' => 'Investing in safer streets and better cycle routes isn\'t just good for the environment — it\'s good for our high streets, our health, and our children\'s future.',
        'attribution_name' => 'Priya Chandra',
        'attribution_role' => 'Cabinet Member for Sustainable Transport Delivery',
    ];

    public $examples = [
        'Short' => [
            'quote_size' => 'large',
            'quote' => 'Find the right cycling group for you — there\'s plenty of choice!',
            'attribution_name' => 'Ben',
            'attribution_role' => 'Cyclists in Bristol',
        ],
        'Long' => [
            'quote_size' => 'standard',
            'quote' => "We want to make it safer, easier and more pleasant for people to get around, whether they are walking, wheeling, cycling or using the bus and these schemes will help achieve that. They will improve everyday connections and, at Bear Flat, help buses run more reliably.\n\nIn Royal Victoria Park, we have listened to feedback and refined the plans, retaining vehicle access while delivering better facilities for people walking, wheeling and cycling through the park. The improvements will create safer crossings, better accessibility and a more welcoming environment for everyone.\n\nWe will do our best to reduce disruption to traffic while these are installed and want to thank people for their patience as these improvements are put in.",
            'attribution_name' => 'Councillor Lucy Hodge',
            'attribution_role' => 'Cabinet Member for Sustainable Transport Delivery',
        ],
    ];

    public function with(): array
    {
        return [
            'quoteSize' => $this->quoteSize(),
            'quote' => $this->quote(),
            'attributionName' => $this->attributionName(),
            'attributionRole' => $this->attributionRole(),
        ];
    }

    public function fields(): array
    {
        $fields = Builder::make('quote');

        $fields
            ->addTextarea('quote', [
                'label' => 'Quote',
                'required' => 1,
                'rows' => 4,
                'new_lines' => 'wpautop', // wraps each line in <p> — matches .c-quote__body's p + p spacing for multi-paragraph "Long" quotes
            ])
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

    public function quoteSize()
    {
        return get_field('quote_size') ?: $this->example['quote_size'];
    }

    /**
     * `get_field()` already applies the `quote` field's `new_lines => 'wpautop'`
     * formatting to real saved data; the $example fallback is plain text, so
     * it needs the same wpautop() pass manually to match (real <p> tags for
     * multi-paragraph "Long" quotes).
     */
    public function quote()
    {
        return get_field('quote') ?: wpautop($this->example['quote']);
    }

    public function attributionName()
    {
        return get_field('attribution_name') ?: $this->example['attribution_name'];
    }

    public function attributionRole()
    {
        return get_field('attribution_role') ?: $this->example['attribution_role'];
    }

    /**
     * @link https://developer.wordpress.org/block-editor/how-to-guides/enqueueing-assets-in-the-editor/#editor-content-scripts-and-styles
     */
    public function assets(array $block): void
    {
        //
    }
}
