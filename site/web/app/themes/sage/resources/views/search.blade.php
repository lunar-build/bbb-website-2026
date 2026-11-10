{{--
  Search results (Figma "Sample page -> Search results page"). Built on the
  archive layout and its filter fragment-swap (resources/js/archive-filters.js);
  results reuse the Link Card block's styles. Data: App\View\Composers\Search.
--}}

@extends('layouts.app')

@section('content')
  <div class="o-container">
    <div @class(['c-archive', 'c-search', 'c-archive--no-filters' => ! count($filterGroups)])>
      <div class="c-archive__intro c-search__header">
        <h1 class="c-search__title u-heading-2">{{ __('Search results', 'sage') }}</h1>

        <div class="c-search__summary">
          @if ($query !== '')
            {{-- translators: %s: the search term --}}
            <p class="c-search__query u-standfirst">{{ sprintf(__('You searched for “%s”', 'sage'), $query) }}</p>
          @endif

          <p id="search-count" class="c-search__count c-archive__count u-standfirst" aria-live="polite" aria-atomic="true" data-archive-sync>
            {{ sprintf(_n('%d result', '%d results', $foundPosts, 'sage'), $foundPosts) }}
          </p>
        </div>

        <form role="search" method="get" action="{{ home_url('/') }}" class="c-search__form" aria-label="{{ __('Refine search', 'sage') }}">
          <input type="search" name="s" value="{{ $query }}"
            placeholder="{{ __('Search', 'sage') }}"
            aria-label="{{ __('Search this site', 'sage') }}">
          <button type="submit" class="c-search__submit">
            <x-icon name="search" />
            <span class="c-search__submit-label">{{ __('Search', 'sage') }}</span>
          </button>
        </form>
      </div>

      @if (count($filterGroups))
        <form class="c-archive__filters" method="get" action="{{ home_url('/') }}" data-archive-filters aria-label="{{ __('Filter results', 'sage') }}">
          <input type="hidden" name="s" value="{{ $query }}">
          <x-filter-checkboxes id="search-filters" :groups="$filterGroups" />
        </form>
      @endif

      <div class="c-archive__results" data-archive-results>
        @include('partials.search-results')
      </div>
    </div>
  </div>
@endsection
