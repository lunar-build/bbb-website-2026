@php
    $items = menu_items_to_array('primary_navigation');
    $logoCharcoal = get_field('logo_charcoal', 'option');
    $cta = get_field('cta', 'option');
    $socialLinks = social_links_from_options();
@endphp

{{-- lunar-site-header stays unconstrained (full viewport width) so the
     mobile search band's grey background can bleed edge-to-edge; each row
     that needs the site's max-width/gutter gets its own inner
     .o-container instead of one wrapping the whole header. Per
     base/_container.scss: "don't wrap the whole block in .o-container —
     put it on an inner content wrapper instead" when only part of a block
     needs the full-width background. --}}
<lunar-site-header sticky>
    {{-- Row 1: logo + socials/CTA (desktop) or logo + CTA/search/menu toggle
         (mobile). Row 2: L1 nav. Both are plain block-level siblings inside
         lunar-site-header's default slot, so they stack without needing to
         fight lunar-nav's own internal (shadow DOM, single-row) layout —
         its brand/actionsDesktop slots are unused here. --}}
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
                    <wa-button variant="brand" appearance="accent" pill class="c-header-cta" href="{{ $cta['url'] }}"
                        @if (($cta['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif>
                        {{ $cta['title'] }}
                      <x-icon name="arrow-right" slot="end" />
                    </wa-button>
                @endif
            </div>

            <div class="c-header-actions c-header-actions--mobile">
                @if (! empty($cta['url']))
                    <wa-button variant="brand" appearance="accent" pill with-end class="c-header-cta c-header-cta--small"
                        href="{{ $cta['url'] }}"
                        @if (($cta['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif>
                        Plan route
                        <x-icon name="arrow-right" slot="end" />
                    </wa-button>
                @endif

                {{-- Search band toggle only — no submit wiring yet, this is a
                     placeholder ahead of the real search component landing in
                     lunar-ui-components (see Phase 4 of the header rebuild plan).
                     aria-label/aria-expanded are updated in header-search.js as
                     the panel opens/closes; the icon swap is decorative only. --}}
                <button type="button" class="c-header-search-toggle" aria-expanded="false" aria-controls="mobile-search"
                    aria-label="Search">
                    <x-icon name="search" class="c-header-search-toggle__icon-search" />
                    <x-icon name="close" class="c-header-search-toggle__icon-close" />
                </button>

                {{-- Opens the mobile nav overlay (#primary-menu, below). Icon
                     swaps menu<->close and aria-expanded is updated in
                     primary-nav.js. --}}
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

    {{-- Desktop nav (mobile hides this via components/_primary-nav.scss; the
         bespoke overlay below takes over there instead). no-collapse keeps
         lunar-nav from ever showing its own hamburger/collapsing — it was
         producing a second, redundant toggle alongside .c-header-menu-toggle
         once the mobile overlay existed. --}}
    <div class="o-container c-primary-nav-desktop">
        <lunar-nav label="{{ wp_get_nav_menu_name('primary_navigation') ?: 'Primary' }}"
            items="{{ json_encode($items) }}" no-collapse>
        </lunar-nav>
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
@include('sections.primary-nav', ['items' => $items, 'cta' => $cta, 'socialLinks' => $socialLinks])
