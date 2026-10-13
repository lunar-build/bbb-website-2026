<?php

namespace App\View\Composers;

use App\Support\ContentHero;
use App\Support\MapListingFilters;
use App\Support\OpenData;
use Roots\Acorn\View\Composer;

class MapListing extends Composer
{
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
        $content = ContentHero::forPost(get_the_ID());

        return [
            'hero' => $content['hero'],
            'body' => $content['body'],
            'filterGroups' => MapListingFilters::groups($all, $active),
            'resultsHeading' => get_field('map_listing_heading') ?: OpenData::DATASETS[$dataset]['label'] ?? '',
            'foundPosts' => count($items),
            'cards' => array_map(fn($item) => $this->card($item), $items),
            'markers' => array_values(array_filter(array_map(fn($item) => $item['lat'] === null ? null : [
                'id' => "map-item-{$item['id']}",
                'name' => $item['name'],
                'address' => $item['address'],
                'lat' => $item['lat'],
                'lng' => $item['lng'],
            ], $items))),
            'mapsKey' => get_field('google_maps_api_key', 'option'),
            'mapId' => get_field('google_maps_map_id', 'option'),
        ];
    }

    /**
     * An item shaped for <x-filter-result-card>.
     */
    protected function card(array $item): array
    {
        $details = array_values(array_filter([
            $item['address'] ? ['icon' => 'map-pin', 'text' => $item['address']] : null,
            $item['phone'] ? ['icon' => 'phone', 'text' => $item['phone'], 'url' => 'tel:' . preg_replace('/[^0-9+]/', '', $item['phone'])] : null,
            $item['type'] ? ['icon' => 'info', 'text' => $item['type']] : null,
        ]));

        return [
            'id' => "map-item-{$item['id']}",
            'title' => $item['name'],
            'details' => $details,
            'services' => array_map(fn($service) => [
                'label' => $service,
                'icon' => $this->serviceIcon($service),
            ], $item['services']),
            // Pumps have no description — who maintains them is their summary.
            'description' => $item['description'] ?: $item['maintained'],
            'url' => $item['website'],
        ];
    }

    /**
     * Figma only supplies icons for some services — the rest share the bike.
     */
    protected function serviceIcon(string $service): string
    {
        $icon = 'service-' . sanitize_title($service);

        return file_exists(get_theme_file_path("resources/svg/{$icon}.svg")) ? $icon : 'service-secondhand-bikes';
    }
}
