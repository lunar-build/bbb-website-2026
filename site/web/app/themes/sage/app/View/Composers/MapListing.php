<?php

namespace App\View\Composers;

use App\Blocks\Concerns\HasBusinessCardFields;
use App\Support\Breadcrumbs;
use App\Support\ContentHero;
use App\Support\MapListingFilters;
use App\Support\OpenData;
use App\Support\PageMenu;
use Roots\Acorn\View\Composer;

class MapListing extends Composer
{
    use HasBusinessCardFields;

    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'template-map-listing',
    ];

    /**
     * Data to be passed to view before rendering.
     */
    public function with(): array
    {
        $dataset = get_field('map_listing_dataset') ?: 'shops';
        $all = OpenData::items($dataset);
        $active = MapListingFilters::active((array) get_field('map_listing_services'));
        $items = MapListingFilters::apply($all, $active);
        // Before the content renders: that's what switches on the headings' anchor ids.
        $pageMenuItems = PageMenu::forPost();
        $content = ContentHero::forPost(get_the_ID());
        $cards = array_map(fn($item) => $this->card($item, $dataset), $items);

        return [
            'hero' => $content['hero'] ?: ContentHero::fallback(get_the_title(), Breadcrumbs::forPost()),
            'body' => $content['body'],
            'pageMenuItems' => $pageMenuItems,
            'filterGroups' => MapListingFilters::groups($all, $active),
            'resultsHeading' => get_field('map_listing_heading') ?: OpenData::DATASETS[$dataset]['label'] ?? '',
            'foundPosts' => count($items),
            'cards' => $cards,
            'markers' => array_values(array_filter(array_map(fn($item, $card) => $item['lat'] === null ? null : [
                'name' => $item['name'],
                'lat' => $item['lat'],
                'lng' => $item['lng'],
                // The pin's popup — same details as its card, rendered here so Blade escapes it.
                'info' => view('partials.map-listing-info', ['card' => $card])->render(),
            ], $items, $cards))),
            'mapsKey' => get_field('google_maps_api_key', 'option'),
            'mapId' => get_field('google_maps_map_id', 'option'),
        ];
    }

    /**
     * Shop services → the Business Card's Web Awesome service icons.
     */
    protected const SERVICE_ICONS = [
        'New bikes' => 'shop',
        'Secondhand bikes' => 'recycle',
        'Donate a bike' => 'hand-holding-heart',
        'Bike servicing' => 'wrench',
        'Mobile bike repairs' => 'wrench',
        'Bike hire' => 'gift',
    ];

    /**
     * Google Maps' keyless link that opens Street View at a point.
     */
    protected function streetViewUrl(float $lat, float $lng): string
    {
        return 'https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=' . $lat . ',' . $lng;
    }

    /**
     * An item shaped for <x-business-card>.
     */
    protected function card(array $item, string $dataset): array
    {
        $contactInfo = array_values(array_filter([
            $item['address'] ? ['icon' => 'location-dot', 'text' => $item['address']] : null,
            $item['phone'] ? ['icon' => 'phone', 'text' => $item['phone']] : null,
            $item['type'] ? ['icon' => 'circle-info', 'text' => $item['type']] : null,
        ]));

        return [
            'id' => "map-item-{$item['id']}",
            'name' => $item['name'],
            'contactInfo' => [
                ...$this->withBusinessCardContactHrefs($contactInfo),
                // The council's pump data has no photos, so point at Street View instead.
                ...($dataset === 'pumps' && $item['lat'] !== null && $item['lng'] !== null ? [[
                    'icon' => 'street-view',
                    'text' => __('View in Street View', 'sage'),
                    'href' => $this->streetViewUrl($item['lat'], $item['lng']),
                ]] : []),
            ],
            'services' => array_map(fn($service) => [
                'icon' => self::SERVICE_ICONS[$service] ?? 'bicycle',
                'text' => $service,
            ], $item['services']),
            // Pumps have no description — who maintains them is their summary.
            'description' => $item['description'] ?: $item['maintained'],
            'regions' => $item['regions'],
            'link' => $item['website'] ? [
                'title' => __('Learn more (opens in a new tab)', 'sage'),
                'url' => $item['website'],
                'target' => '_blank',
            ] : null,
        ];
    }
}
