{{--
  Template Name: Sticky Nav
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
  theme already works.

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
      <div class="c-sticky-nav-template">
        <div class="c-sticky-nav-template__nav">
          <x-sticky-page-menu :items="$pageMenuItems" data-sticky-nav-menu />
        </div>

        <div class="c-sticky-nav-template__content" data-sticky-nav-content>
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
