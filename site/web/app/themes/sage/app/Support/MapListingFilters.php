<?php

namespace App\Support;

class MapListingFilters
{
    /**
     * Shop services grouped as in Figma's "Filter page" sidebar. Services the
     * source data adds later land in an "Other services" group.
     */
    public const SERVICE_GROUPS = [
        'Categories' => ['New bikes', 'Secondhand bikes', 'E-Bikes', 'Custom bikes', 'Donate a bike'],
        'Fix your bike' => ['Bike servicing', 'Mobile bike repairs'],
        'Rent a bike' => ['Bike hire', 'Bike tours'],
    ];

    /**
     * Selected location/service slugs. With no filter params at all (first
     * visit) the page's preset services apply; the form always submits
     * `filtered=1`, so unticking everything doesn't bring the presets back.
     * The old site's comma-separated ?location=&category= links still work.
     *
     * @return array{location: string[], service: string[]}
     */
    public static function active(array $presetServices = []): array
    {
        $read = fn($param, $legacy) => array_values(array_filter(array_map('sanitize_title', [
            ...(array) ($_GET["filter_{$param}"] ?? []),
            ...explode(',', (string) ($_GET[$legacy] ?? '')),
        ])));

        $active = [
            'location' => $read('location', 'location'),
            'service' => $read('service', 'category'),
        ];

        if (! isset($_GET['filtered']) && ! $active['location'] && ! $active['service']) {
            $active['service'] = array_map('sanitize_title', $presetServices);
        }

        return $active;
    }

    /**
     * OR within a checkbox group, AND across groups — same as the post archives.
     */
    public static function apply(array $items, array $active): array
    {
        $byGroup = collect($active['service'])->groupBy(fn($slug) => self::serviceGroup($slug));

        return array_values(array_filter($items, function ($item) use ($active, $byGroup) {
            $regions = array_map('sanitize_title', $item['regions']);
            $services = array_map('sanitize_title', $item['services']);

            if ($active['location'] && ! array_intersect($regions, $active['location'])) {
                return false;
            }

            foreach ($byGroup as $slugs) {
                if (! array_intersect($services, $slugs->all())) {
                    return false;
                }
            }

            return true;
        }));
    }

    /**
     * Groups shaped for <x-filter-checkboxes>, built from the values present
     * in the data. Only shops carry services.
     */
    public static function groups(array $items, array $active): array
    {
        $item = fn($param, $label) => [
            'label' => $label,
            'name' => "filter_{$param}[]",
            'value' => sanitize_title($label),
            'id' => "filter_{$param}-" . sanitize_title($label),
            'checked' => in_array(sanitize_title($label), $active[$param], true),
        ];

        $regions = collect($items)->pluck('regions')->flatten()->unique()->sort()->values();
        $groups = $regions->isNotEmpty() ? [[
            'heading' => __('Filter by location', 'sage'),
            'items' => $regions->map(fn($region) => $item('location', $region))->all(),
        ]] : [];

        $services = collect($items)->pluck('services')->flatten()->unique();
        $known = collect(self::SERVICE_GROUPS)->flatten();

        foreach (self::SERVICE_GROUPS as $heading => $labels) {
            if ($present = array_values(array_filter($labels, fn($label) => $services->contains($label)))) {
                $groups[] = ['heading' => __($heading, 'sage'), 'items' => array_map(fn($label) => $item('service', $label), $present)];
            }
        }

        if (($other = $services->diff($known)->sort()->values())->isNotEmpty()) {
            $groups[] = ['heading' => __('Other services', 'sage'), 'items' => $other->map(fn($label) => $item('service', $label))->all()];
        }

        return $groups;
    }

    protected static function serviceGroup(string $slug): string
    {
        foreach (self::SERVICE_GROUPS as $heading => $labels) {
            if (in_array($slug, array_map('sanitize_title', $labels), true)) {
                return $heading;
            }
        }

        return 'Other services';
    }
}
