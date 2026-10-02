<?php

namespace App\Blocks;

use App\Blocks\Concerns\HasBusinessCardFields;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class BusinessCard extends Block
{
    use HasBusinessCardFields;

    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Business Card';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'business-card';

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
        'business',
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
        $fields = Builder::make('business_card');

        $this->addBusinessCardFields($fields);

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

        return $this->withBusinessCardContactHrefs($rows);
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
