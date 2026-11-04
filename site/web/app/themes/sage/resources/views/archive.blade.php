{{--
  One template for every archive: News (posts page + category archives) and
  CPT archives. Hero + intro come from a linked page (Settings → Reading's
  Posts page for News, Theme Options → Archive pages for CPTs) — see
  App\View\Composers\Archive. If that page holds an Events Calendar block, its
  content replaces the filters and results (App\Support\ArchiveCalendar). Filtering: App\Support\ArchiveFilters
  (server) + resources/js/archive-filters.js (fragment swap).
--}}

@extends('layouts.app')

@section('content')
  {{-- The page's own hero block, or a Text Hero with its title (App\Support\ContentHero). --}}
  {!! $hero !!}

  @if ($showsCalendar)
    {{-- The linked page holds an Events Calendar block (App\Support\ArchiveCalendar): the page's blocks are the
         archive, each bringing its own container and spacing. --}}
    {!! $body !!}
  @else
    <div class="o-container">
      <div @class(['c-archive', 'c-archive--no-filters' => ! count($filterGroups)])>
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
  @endif
@endsection
