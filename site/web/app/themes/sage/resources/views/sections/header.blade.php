@php
    $items = menu_items_to_array('primary_navigation');
    $logoCharcoal = get_field('logo_charcoal', 'option');
    $cta = get_field('cta', 'option');
    $ctaMobile = get_field('cta_mobile', 'option') ?: $cta;
    $socialLinks = social_links_from_options();
@endphp

{{-- lunar-site-header stays unconstrained; rows that need the site gutter
     get their own inner .o-container instead of one wrapping everything. --}}
<lunar-site-header sticky>
    <div class="o-container">
        <div class="c-header-top">
            <a class="c-header-top__logo" href="{{ home_url('/') }}" aria-label="{{ get_bloginfo('name') }}">
                @if ($logoCharcoal)
                    <img src="{{ $logoCharcoal['url'] }}" alt="" class="c-logo">
                @else
                    <x-icon name="logo" class="c-logo" />
                @endif
            </a>

            <div class="c-header-actions c-header-actions--desktop">
                @foreach ($socialLinks as $link)
                    <a href="{{ $link['url'] }}" class="c-header-actions__social" aria-label="{{ ucfirst($link['platform']) }}"
                        target="_blank" rel="noopener noreferrer">
                        <x-icon name="{{ $link['platform'] }}" />
                    </a>
                @endforeach

                @if (! empty($cta['url']))
                    <wa-button variant="brand" appearance="accent" pill href="{{ $cta['url'] }}"
                        @if (($cta['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif>
                        {{ $cta['title'] }}
                      <x-icon name="arrow-right" slot="end" />
                    </wa-button>
                @endif
            </div>

            <div class="c-header-actions c-header-actions--mobile">
                @if (! empty($ctaMobile['url']))
                    <wa-button variant="brand" appearance="accent" pill with-end class="c-header-cta c-header-cta--small"
                        href="{{ $ctaMobile['url'] }}"
                        @if (($ctaMobile['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif>
                        {{ $ctaMobile['title'] }}
                        <x-icon name="arrow-right" slot="end" />
                    </wa-button>
                @endif

                {{-- Placeholder ahead of the real search component (Phase 4) --}}
                <button type="button" class="c-header-search-toggle" aria-expanded="false" aria-controls="mobile-search"
                    aria-label="Search">
                    <x-icon name="search" class="c-header-search-toggle__icon-search" />
                    <x-icon name="close" class="c-header-search-toggle__icon-close" />
                </button>

                <button type="button" class="c-header-menu-toggle" aria-expanded="false" aria-controls="primary-menu"
                    aria-label="Menu">
                    <x-icon name="menu" class="c-header-menu-toggle__icon-menu" />
                    <x-icon name="close" class="c-header-menu-toggle__icon-close" />
                </button>
            </div>
        </div>
    </div>

    <div id="mobile-search" class="c-header-search" hidden>
        <div class="o-container">
            <form role="search" aria-label="Site" method="get" action="{{ home_url('/') }}" class="c-header-search__form">
                <x-input type="search" name="s" placeholder="Search for a keyword" icon="search"
                    icon-button-label="Search" aria-label="Search for a keyword" />
            </form>
        </div>
    </div>

</lunar-site-header>

{{-- Deliberately OUTSIDE <lunar-site-header>: that component's shadow
     <header> always carries a `transform` (part of its sticky auto-hide),
     and any transformed ancestor becomes the containing block for a
     position:fixed descendant — sharing it meant this overlay slid away
     together with the header on scroll, and lost the stacking-order fight
     for z-index against the header's own (non-positioned, so effectively
     z-index:auto) content, hiding the close icon behind it. As a proper
     sibling, position:fixed here is contained by the real viewport, and
     z-index compares correctly against lunar-site-header's own
     (--lunar-site-header-z-index, 100). --}}
@include('sections.primary-nav', ['items' => $items, 'cta' => $ctaMobile, 'socialLinks' => $socialLinks])
