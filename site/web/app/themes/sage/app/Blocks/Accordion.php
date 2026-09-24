<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class Accordion extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Accordion';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'accordion';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A list of expandable, collapsible content items.';

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
    public $icon = 'editor-help';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'accordion',
        'expand',
        'collapse',
        'faq',
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
        'heading' => 'Frequently asked questions',
        'items' => [
            [
                'title' => 'Do you have bikes that I can use?',
                'content' => '<p>Yes. We have a large range of inclusive wheels for all cycles available, including trikes, recumbents, two-seater bikes, hand cycles and a platform wheelchair-accessible bike. These speciality cycles are available for anyone who needs them during all of our cycling sessions.</p>'
                    .'<p>We also have two-wheeled bikes for adults and children, as well as:</p>'
                    .'<ul><li>Balance bikes</li><li>Trailers</li><li>Bike seats</li><li>Tag-alongs</li></ul>'
                    .'<p>For more information about our cycles <a href="#">click here</a>.</p>'
                    .'<p>There is no charge for using our cycles but at extremely busy times people may need to share. If you do bring your own bike you are still welcome to try ours.</p>',
            ],
            [
                'title' => 'Do you sell bikes at Bristol Cycling Centre?',
                'content' => '<p>No, but our friendly team can point you toward local retailers and workshops if you\'re looking to buy or service a bike.</p>',
            ],
            [
                'title' => 'What about helmets?',
                'content' => '<p>Helmets are available to borrow free of charge alongside any bike hire, in a range of sizes for adults and children.</p>',
            ],
            [
                'title' => 'Do I need to pre-book?',
                'content' => '<p>Pre-booking is recommended, especially at weekends and during school holidays, but drop-ins are welcome whenever we have availability.</p>',
            ],
        ],
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'heading' => $this->heading(),
            'items' => $this->items(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('accordion');

        $fields
            ->addText('heading', [
                'label' => 'Heading',
            ])
            ->addRepeater('items', [
                'label' => 'Items',
                'button_label' => 'Add item',
                'min' => 1,
                'layout' => 'block',
            ])
                ->addText('title', [
                    'label' => 'Title',
                    'required' => 1,
                ])
                ->addWysiwyg('content', [
                    'label' => 'Content',
                    'required' => 1,
                    'tabs' => 'visual',
                    'media_upload' => 0,
                    'toolbar' => 'full',
                ])
            ->endRepeater();

        return $fields->build();
    }

    /**
     * Retrieve the heading.
     *
     * @return string
     */
    public function heading()
    {
        return get_field('heading') ?: $this->example['heading'];
    }

    /**
     * Retrieve the accordion items.
     *
     * @return array
     */
    public function items()
    {
        return get_field('items') ?: $this->example['items'];
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
