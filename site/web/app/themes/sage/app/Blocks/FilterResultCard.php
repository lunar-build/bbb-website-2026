<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class FilterResultCard extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Filter Result Card';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'filter-result-card';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A business/service listing card with contact info, services and a link — no image.';

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
    public $icon = 'location-alt';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'card',
        'filter',
        'directory',
        'listing',
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
     * Icon choices shared by the Contact info repeater — a fixed,
     * developer-controlled set rendered via <wa-icon>, matching how
     * FeatureCard.php's `stats` repeater constrains its `pill_variant`
     * select rather than allowing arbitrary input.
     *
     * @var array
     */
    protected $contactIconChoices = [
        'location-dot' => 'Location',
        'phone' => 'Phone',
        'envelope' => 'Email',
        'globe' => 'Website',
    ];

    /**
     * Icon choices shared by the Services repeater.
     *
     * @var array
     */
    protected $serviceIconChoices = [
        'bicycle' => 'Bicycle',
        'wrench' => 'Servicing/repair',
        'recycle' => 'Secondhand/refurbished',
        'hand-holding-heart' => 'Donate',
        'shop' => 'Shop',
        'gift' => 'Hire/loan',
    ];

    /**
     * The block preview example data.
     *
     * @var array
     */
    public $example = [
        'name' => 'Weston Bicycle Works',
        'contact_info' => [
            ['icon' => 'location-dot', 'text' => '143 Locking Road, Weston-Super-Mare, BS23 3ER'],
            ['icon' => 'phone', 'text' => '01934 629989'],
        ],
        'services' => [
            ['icon' => 'recycle', 'text' => 'Secondhand bikes'],
            ['icon' => 'wrench', 'text' => 'Bike servicing'],
            ['icon' => 'hand-holding-heart', 'text' => 'Donate a bike'],
        ],
        'description' => 'A community enterprise selling quality refurbished bikes and welcome bike donations.',
        'link' => [
            'title' => 'Learn more',
            'url' => 'https://betterbybike.info/weston-bicycle-works/',
            'target' => '_blank',
        ],
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'name' => $this->name(),
            'contactInfo' => $this->contactInfo(),
            'services' => $this->services(),
            'description' => $this->description(),
            'link' => $this->link(),
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('filter_result_card');

        $fields
            ->addText('name', [
                'label' => 'Name',
                'required' => 1,
            ])
            ->addRepeater('contact_info', [
                'label' => 'Contact info',
                'instructions' => 'Address, phone, etc. Leave empty to omit.',
                'button_label' => 'Add contact info',
                'min' => 0,
                'layout' => 'block',
            ])
                ->addSelect('icon', [
                    'label' => 'Icon',
                    'choices' => $this->contactIconChoices,
                    'default_value' => 'location-dot',
                    'ui' => true,
                ])
                ->addText('text', [
                    'label' => 'Text',
                    'required' => 1,
                ])
            ->endRepeater()
            ->addRepeater('services', [
                'label' => 'Services',
                'instructions' => 'Short tag-style service labels. Leave empty to omit.',
                'button_label' => 'Add service',
                'min' => 0,
                'layout' => 'block',
            ])
                ->addSelect('icon', [
                    'label' => 'Icon',
                    'choices' => $this->serviceIconChoices,
                    'default_value' => 'bicycle',
                    'ui' => true,
                ])
                ->addText('text', [
                    'label' => 'Text',
                    'required' => 1,
                ])
            ->endRepeater()
            ->addTextarea('description', [
                'label' => 'Description',
                'rows' => 3,
            ])
            ->addLink('link', [
                'label' => 'Link',
                'instructions' => 'Link text + URL for the CTA.',
                'required' => true,
            ]);

        return $fields->build();
    }

    /**
     * Retrieve the name.
     *
     * @return string
     */
    public function name()
    {
        return get_field('name') ?: $this->example['name'];
    }

    /**
     * Retrieve the contact info rows, each augmented with an `href` so
     * phone/email/address/website rows are directly actionable (tap to
     * call, open the default mail client, open in Maps, or open the site)
     * rather than just being static text.
     *
     * @return array
     */
    public function contactInfo()
    {
        $rows = get_field('contact_info');

        if (! $rows) {
            $rows = $this->preview ? $this->example['contact_info'] : [];
        }

        return array_map(fn($row) => $row + ['href' => $this->contactHref($row['icon'], $row['text'])], $rows);
    }

    /**
     * Build the tap/click-through URL for a contact info row based on its
     * icon type.
     *
     * @param  string  $icon
     * @param  string  $text
     * @return string|null
     */
    protected function contactHref($icon, $text)
    {
        return match ($icon) {
            'phone' => 'tel:' . preg_replace('/[^0-9+]/', '', $text),
            'envelope' => 'mailto:' . trim($text),
            'location-dot' => 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($text),
            'globe' => preg_match('#^https?://#i', $text) ? $text : 'https://' . $text,
            default => null,
        };
    }

    /**
     * Retrieve the services rows.
     *
     * @return array
     */
    public function services()
    {
        $rows = get_field('services');

        if (! $rows) {
            $rows = $this->preview ? $this->example['services'] : [];
        }

        return $rows;
    }

    /**
     * Retrieve the description.
     *
     * @return string|null
     */
    public function description()
    {
        $description = get_field('description');

        if ($description) {
            return $description;
        }

        return $this->preview ? $this->example['description'] : null;
    }

    /**
     * Retrieve the link.
     *
     * @return array
     */
    public function link()
    {
        return get_field('link') ?: $this->example['link'];
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
