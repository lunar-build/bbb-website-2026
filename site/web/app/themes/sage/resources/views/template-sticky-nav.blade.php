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
  Top nav (sections.header, via layouts.app) → hero → sticky left page-menu
  beside the right-hand content column → footer. For pages like "Report a
  road fault" or "Regional cycle maps" that need a persistent jump menu
  alongside their content.

  Hero: no dedicated hero markup here — editors add the existing "Image
  Hero" block as the first block in the page's content, same as any other
  page. Keeping the hero block-editor-driven (rather than template fields)
  means it stays consistent with how every other hero use case in this
  theme already works. Leave the Image Hero block's own "Show breadcrumbs"
  toggle off on this template — breadcrumbs render below instead (see
  next paragraph), matching the Figma design where they sit at the top of
  the content column, inline with the sticky nav's top, not inside the
  hero overlay.

  Breadcrumbs: hardcoded here (not editor-driven) as the first element in
  the content column, so its top edge lines up with the sticky nav's —
  both are sibling grid items starting on the same row.

  Page menu: items are generated from this page's top-level (H2) headings
  by App\Support\PageMenu (see app/View/Composers/StickyNavTemplate.php),
  preferring an editor-set Gutenberg "HTML anchor" per heading and falling
  back to an auto-slug of the heading text — see the matching
  render_block_core/heading filter in app/filters.php that injects that
  fallback slug as the rendered heading's `id`. The current-section
  highlight is CSS (components/_sticky-page-menu.scss's
  .c-sticky-page-menu__item--active), toggled client-side as the reader
  scrolls by resources/js/sticky-nav-template.js.
--}}

@extends('layouts.app')

@section('content')
  @while(have_posts()) @php(the_post())
    @if ($hero)
      {!! $hero !!}
    @else
      {{-- No Image Hero block at the top of the content — fall back to a
      plain <h1>, same as template-custom, so the page still has one. --}}
      @include('partials.page-header')
    @endif

    <div class="o-container">
      {{-- No H2 headings means no page-menu items (App\Support\PageMenu returns
      []) — skip the two-column grid entirely rather than leaving an empty,
      collapsed nav column with its grid gap still showing as a stray gap
      with nothing in it. --}}
      <div @if (count($pageMenuItems)) class="c-sticky-nav-template" @endif>
        @if (count($pageMenuItems))
          <div class="c-sticky-nav-template__nav">
            <x-sticky-page-menu :items="$pageMenuItems" data-sticky-nav-menu />
          </div>
        @endif

        <div class="c-sticky-nav-template__content">
          <x-breadcrumbs :items="App\Support\Breadcrumbs::forPost()" />

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
