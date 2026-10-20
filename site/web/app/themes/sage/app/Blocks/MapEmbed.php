<?php

namespace App\Blocks;

use App\Blocks\Concerns\ResolvesFields;
use Log1x\AcfComposer\Block;
use Log1x\AcfComposer\Builder;

class MapEmbed extends Block
{
    use ResolvesFields;

    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Map Embed';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'map-embed';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'An embedded Google map (Google Maps or My Maps share link), shown full width at a chosen height.';

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
    public $icon = 'location-alt';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = [
        'map',
        'google',
        'embed',
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
        'map_url' => 'https://www.google.com/maps/d/embed?mid=1AsdljNy_h4vBoPYJYMxipqd0eblR6n4',
        'map_title' => 'Map of Bristol Cycling Centre sessions',
        'height' => 'medium',
    ];

    /**
     * Heights in rem, keyed by the height field's choices.
     */
    protected const HEIGHTS = [
        'small' => 25,
        'medium' => 33.75,
        'large' => 45,
    ];

    /**
     * Data to be passed to the block before rendering.
     */
    public function with(): array
    {
        return [
            'src' => $this->src(),
            'mapTitle' => $this->resolveField('map_title', ''),
            'height' => self::HEIGHTS[$this->resolveField('height', 'medium')] ?? self::HEIGHTS['medium'],
        ];
    }

    /**
     * The block field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('map_embed');

        $fields
            ->addUrl('map_url', [
                'label' => 'Map link',
                'instructions' => 'A Google Maps or Google My Maps link. In Google Maps use Share → Embed a map and copy the address inside src="…", or paste a My Maps link.',
                'required' => true,
            ])
            ->addText('map_title', [
                'label' => 'Map description',
                'instructions' => 'Read out by screen readers, e.g. "Map of public bike pumps in Bristol".',
                'required' => true,
            ])
            ->addSelect('height', [
                'label' => 'Height',
                'choices' => [
                    'small' => 'Small',
                    'medium' => 'Medium',
                    'large' => 'Large',
                ],
                'default_value' => 'medium',
                'ui' => true,
            ]);

        return $fields->build();
    }

    /**
     * Embeddable iframe URL, or '' for anything that isn't a Google map — the
     * iframe would otherwise load whatever an editor pastes.
     */
    public function src(): string
    {
        $url = trim((string) $this->resolveField('map_url', ''));
        $parts = wp_parse_url($url);
        $host = $parts['host'] ?? '';

        if (($parts['scheme'] ?? '') !== 'https' || ! preg_match('/(^|\.)google\.[a-z.]+$/', $host) || ! str_starts_with($parts['path'] ?? '', '/maps')) {
            return '';
        }

        // My Maps share links (/maps/d/viewer or /edit) only embed from /maps/d/embed.
        if (preg_match('#^/maps/d/(viewer|edit)#', $parts['path'])) {
            $url = preg_replace('#/maps/d/(viewer|edit)#', '/maps/d/embed', $url, 1);
        }

        return $url;
    }
}
