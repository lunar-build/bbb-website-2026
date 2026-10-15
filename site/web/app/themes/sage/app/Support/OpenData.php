<?php

namespace App\Support;

class OpenData
{
    /**
     * Bristol City Council's open-data ArcGIS layers behind the Map Listing
     * template. Page requests only ever read the cached copy — refresh()
     * runs hourly on WP cron, or on demand via `wp opendata refresh`.
     */
    public const DATASETS = [
        'shops' => ['layer' => 14, 'label' => 'Bike shops and repairs'],
        'bmx' => ['layer' => 203, 'label' => 'BMX facilities'],
        'pumps' => ['layer' => 201, 'label' => 'Bike pumps'],
    ];

    protected const URL = 'https://maps2.bristol.gov.uk/server2/rest/services/ext/datagov/FeatureServer/%d/query?outFields=*&where=1%%3D1&f=geojson';

    /**
     * Cached, normalised items for a dataset. Fetches once if nothing has
     * been cached yet (fresh install), so the page never starts empty.
     *
     * @return array<int, array>
     */
    public static function items(string $dataset): array
    {
        $cached = get_option(self::optionName($dataset));

        if (! is_array($cached)) {
            $cached = self::refresh($dataset) ? get_option(self::optionName($dataset)) : null;
        }

        return $cached['items'] ?? [];
    }

    /**
     * Fetch and cache one dataset. A failed or empty fetch keeps the
     * previous cache rather than blanking the page.
     */
    public static function refresh(string $dataset): bool
    {
        if (! isset(self::DATASETS[$dataset])) {
            return false;
        }

        $response = wp_remote_get(sprintf(self::URL, self::DATASETS[$dataset]['layer']), ['timeout' => 20]);
        $features = json_decode(wp_remote_retrieve_body($response), true)['features'] ?? null;

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200 || ! $features) {
            return false;
        }

        $items = array_values(array_filter(array_map([self::class, 'normalise'], $features)));

        usort($items, fn($a, $b) => strcasecmp($a['name'], $b['name']));

        update_option(self::optionName($dataset), ['fetched' => time(), 'items' => $items], false);

        return true;
    }

    /**
     * One GeoJSON feature → a flat item. Layers use different property
     * names and casing, and comma-separate multi-value fields.
     */
    protected static function normalise(array $feature): ?array
    {
        $fields = array_change_key_case($feature['properties'] ?? []);
        $name = trim($fields['name'] ?? '');
        [$lng, $lat] = $feature['geometry']['coordinates'] ?? [null, null];

        // Nameless rows are half-entered records in the source data.
        if (! $name) {
            return null;
        }

        $text = fn($key) => trim(str_replace('¿', "'", (string) ($fields[$key] ?? '')));
        $list = fn($key) => array_values(array_filter(array_map('trim', explode(',', $text($key)))));

        return [
            'id' => (int) ($fields['objectid'] ?? 0),
            'name' => $name,
            'address' => $text('address'),
            'description' => $text('description'),
            'website' => esc_url_raw($text('website')),
            'phone' => $text('phone'),
            'services' => $list('services_provided'),
            'regions' => $list('region'),
            'type' => $text('type'),
            'maintained' => $text('maintained'),
            'lat' => is_numeric($lat) ? (float) $lat : null,
            'lng' => is_numeric($lng) ? (float) $lng : null,
        ];
    }

    protected static function optionName(string $dataset): string
    {
        return "opendata_{$dataset}";
    }
}
