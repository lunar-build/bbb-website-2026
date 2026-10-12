{{--
  One template for every archive: News (posts page + category archives) and
  CPT archives. Hero + intro come from a linked page (Settings → Reading's
  Posts page for News, Theme Options → Archive pages for CPTs) — see
  App\View\Composers\Archive. Filtering: App\Support\ArchiveFilters
  (server) + resources/js/archive-filters.js (fragment swap).
--}}

@extends('layouts.app')

@section('content')
  @if ($hero)
    {!! $hero !!}
  @else
    @include('partials.page-header')
  @endif

  <div class="o-container">
    <div @class(['c-archive', 'c-archive--no-filters' => ! count($filterGroups)])>
      @if (! $hero)
        <x-breadcrumbs class="c-archive__breadcrumbs" :items="$breadcrumbs" />
      @endif

      @if (trim($body))
        <div class="c-archive__intro">
          {!! $body !!}
        </div>
      @endif

      @if (count($filterGroups))
        <form class="c-archive__filters" method="get" action="{{ $formAction }}" data-archive-filters>
          <x-filter-checkboxes id="archive-filters" :groups="$filterGroups" />
        </form>
      @endif

      <div class="c-archive__results" data-archive-results>
        @include('partials.archive-results')
      </div>
    </div>
  </div>
@endsection
