<?php

namespace App\Fields;

use App\Support\MapListingFilters;
use App\Support\OpenData;
use Log1x\AcfComposer\Builder;
use Log1x\AcfComposer\Field;

class MapListing extends Field
{
    /**
     * The field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('map_listing', [
            'title' => 'Map listing',
            'position' => 'side',
        ]);

        $fields
            ->setLocation('page_template', '==', 'template-map-listing.blade.php');

        $services = collect(MapListingFilters::SERVICE_GROUPS)->flatten()->all();

        $fields
            ->addSelect('map_listing_dataset', [
                'label' => 'Dataset',
                'choices' => array_map(fn($dataset) => $dataset['label'], OpenData::DATASETS),
                'default_value' => 'shops',
                'required' => true,
            ])
            ->addText('map_listing_heading', [
                'label' => 'Results heading',
                'instructions' => 'Shown above the results, e.g. "List of bike shops".',
            ])
            ->addCheckbox('map_listing_services', [
                'label' => 'Pre-selected services',
                'instructions' => 'Ticked on first visit, e.g. only "Bike hire" and "Bike tours" for a bike hire page. Visitors can still change them.',
                'choices' => array_combine($services, $services),
                'conditional_logic' => [[['field' => 'map_listing_dataset', 'operator' => '==', 'value' => 'shops']]],
            ]);

        return $fields->build();
    }
}
