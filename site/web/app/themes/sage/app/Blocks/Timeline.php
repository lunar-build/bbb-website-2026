<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class Timeline extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Timeline';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'timeline';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A vertical timeline of numbered steps or chronological events.';

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
    public $icon = 'list-view';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'timeline',
        'steps',
        'process',
        'history',
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
        'heading' => 'How does it work?',
        'intro' => 'A few simple steps to sign up to Love to Ride',
        'steps' => [
            [
                'label' => '',
                'heading' => 'Register for Love to Ride',
                'body' => 'To register see link below',
            ],
            [
                'label' => '',
                'heading' => 'On your Love to Ride profile',
                'body' => "Set a goal for how much you want to get out and ride this month – remember to keep safe by sticking to government guidelines and maintain at least 2m distance from others.",
            ],
            [
                'label' => '',
                'heading' => 'Get inspired on where to ride',
                'body' => '...with local route information for new and regular riders.',
            ],
            [
                'label' => '',
                'heading' => 'Share your stories',
                'body' => "...with other members of how you've stayed biking and help build a strong, supportive riding community!",
            ],
            [
                'label' => '',
                'heading' => 'Invite others!',
                'body' => '...to join!',
            ],
        ],
    ];

    public function with(): array
    {
        return [
            'heading' => $this->heading(),
            'intro' => $this->intro(),
            'steps' => $this->steps(),
        ];
    }

    public function fields(): array
    {
        $fields = Builder::make('timeline');

        $fields
            ->addText('heading', [
                'label' => 'Heading',
                'required' => 0,
            ])
            ->addText('intro', [
                'label' => 'Intro',
                'instructions' => 'Optional standfirst shown under the heading.',
                'required' => 0,
            ])
            ->addRepeater('steps', [
                'label' => 'Steps',
                'button_label' => 'Add step',
                'min' => 1,
                'layout' => 'block',
            ])
                ->addText('label', [
                    'label' => 'Label',
                    'instructions' => 'Optional — shown in the numbered marker instead of the step\'s position (e.g. a date). Leave blank to show the step number.',
                    'required' => 0,
                ])
                ->addText('heading', [
                    'label' => 'Heading',
                    'required' => 1,
                ])
                ->addTextarea('body', [
                    'label' => 'Body',
                    'rows' => 3,
                    'new_lines' => 'br',
                    'required' => 0,
                ])
            ->endRepeater();

        return $fields->build();
    }

    public function heading()
    {
        return get_field('heading') ?: $this->example['heading'];
    }

    public function intro()
    {
        return get_field('intro') ?: $this->example['intro'];
    }

    public function steps()
    {
        return get_field('steps') ?: $this->example['steps'];
    }

    /**
     * @link https://developer.wordpress.org/block-editor/how-to-guides/enqueueing-assets-in-the-editor/#editor-content-scripts-and-styles
     */
    public function assets(array $block): void
    {
        //
    }
}
