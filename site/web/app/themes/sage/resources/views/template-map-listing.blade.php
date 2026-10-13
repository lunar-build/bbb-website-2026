{{--
  Template Name: Map Listing
--}}

{{--
  Shops, BMX facilities and bike pumps from Bristol's open data (Figma
  "Sample page -> Filter page"). The page picks its dataset and preset
  services in the "Map listing" sidebar fields; its own Image Hero and
  intro blocks render above. Data: App\Support\OpenData (cached hourly),
  filtering: App\Support\MapListingFilters, both via
  App\View\Composers\MapListing. Same layout and fragment-swap filtering
  as archive.blade.php; resources/js/map-listing.js draws the map.
--}}

@extends('layouts.app')

@section('content')
  @while(have_posts()) @php(the_post())
    @if ($hero)
      {!! $hero !!}
    @else
      @include('partials.page-header')
    @endif

    <div class="o-container">
      <div @class(['c-archive', 'c-archive--no-filters' => ! count($filterGroups)])>
        @if (! $hero)
          <x-breadcrumbs class="c-archive__breadcrumbs" :items="App\Support\Breadcrumbs::forPost()" />
        @endif

        @if (trim($body))
          <div class="c-archive__intro">
            {!! $body !!}
          </div>
        @endif

        @if (count($filterGroups))
          <form class="c-archive__filters" method="get" action="{{ get_permalink() }}" data-archive-filters>
            <input type="hidden" name="filtered" value="1">
            <x-filter-checkboxes id="map-listing-filters" :groups="$filterGroups" />
          </form>
        @endif

        <div class="c-archive__results">
          @if ($mapsKey)
            <div
              class="c-map-listing__map"
              data-map-listing
              data-api-key="{{ $mapsKey }}"
              @if ($mapId) data-map-id="{{ $mapId }}" @endif
              role="region"
              aria-label="{{ __('Map of results', 'sage') }}"
            ></div>
          @endif

          <div data-archive-results>
            @include('partials.map-listing-results')
          </div>
        </div>
      </div>
    </div>
  @endwhile
@endsection
