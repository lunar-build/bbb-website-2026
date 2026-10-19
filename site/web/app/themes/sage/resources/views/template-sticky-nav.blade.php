{{--
  Template Name: Sticky Nav
--}}

{{--
  TODO: currently manually selected per-page via Page Attributes → Template
  — fine for the few known one-off pages it's built for so far (e.g.
  "Report a road fault", "Regional cycle maps"), but only tested against
  short/sparse page content. Revisit once there's a fuller real page to
  test against (longer content, more headings, nested blocks), and
  consider whether this should instead be hard-coded as the default
  `single-{cpt}.blade.php` for a future CPT (e.g. Events) if/when any of
  these page types turns out to repeat rather than stay one-off — see
  Trello card https://trello.com/c/nLCSwtXI/72-page-templates.
--}}

{{--
  Hero → breadcrumbs → sticky page menu beside the content (breadcrumbs sit above the
  menu below lg, top of the content column from lg up; see base/_sticky-nav-template.scss).

  Hero: editors add the Image Hero block first in the content, with its "Show breadcrumbs"
  toggle off — breadcrumbs render here instead.

  Page menu: built from the page's H2s by App\Support\PageMenu (via
  View/Composers/StickyNavTemplate.php), using each heading's HTML anchor or an auto-slug
  (render_block_core/heading in app/filters.php). The active highlight is toggled on scroll by
  resources/js/sticky-nav-template.js.
--}}

@extends('layouts.app')

@section('content')
  @while(have_posts()) @php(the_post())
    {{-- The page's own hero block, or a Text Hero with its title (App\Support\ContentHero). --}}
    {!! $hero !!}

    <div class="o-container">
      {{-- No H2 headings means no page-menu items (App\Support\PageMenu returns
      []) — skip the two-column grid entirely rather than leaving an empty,
      collapsed nav column with its grid gap still showing as a stray gap
      with nothing in it. --}}
      <div @if (count($pageMenuItems)) class="c-sticky-nav-template" @endif>
        <div class="c-sticky-nav-template__crumbs">
          <x-breadcrumbs :items="App\Support\Breadcrumbs::forPost()" />
        </div>

        @if (count($pageMenuItems))
          <div class="c-sticky-nav-template__nav">
            <x-sticky-page-menu :items="$pageMenuItems" data-sticky-nav-menu />
          </div>
        @endif

        <div class="c-sticky-nav-template__content">
          {!! $body !!}

          @if ($pagination)
            <nav class="page-nav" aria-label="{{ __('Page', 'sage') }}">
              {!! $pagination !!}
            </nav>
          @endif
        </div>
      </div>
    </div>
  @endwhile
@endsection
