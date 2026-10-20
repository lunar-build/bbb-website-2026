{{-- Desktop bar + dropdown mega-menu (Figma "Desktop" / "Desktop | Dropdown").
     Same $items content model as the mobile overlay (sections/primary-nav.blade.php),
     laid out as columns. Included inside <header class="c-site-header"> so it
     scrolls/sticks with the header — safe there (unlike the mobile overlay)
     since it's static in-flow, not position:fixed. Each dropdown lives inside
     its trigger's own <li> so Tab order flows into it naturally —
     position:absolute still finds .c-primary-nav-desktop as its containing
     block regardless of nesting depth. --}}
<div class="c-primary-nav-desktop">
    <nav class="c-primary-nav-desktop__bar" aria-label="{{ wp_get_nav_menu_name('primary_navigation') ?: 'Primary' }}">
        <div class="o-container">
            <ul class="c-primary-nav-desktop__list" role="list">
                @foreach ($items as $i => $item)
                    <li class="c-primary-nav-desktop__item" data-nav-item>
                        @if (!empty($item['children']))
                            <button type="button" class="c-primary-nav-desktop__link" aria-expanded="false"
                                aria-controls="desktop-dropdown-{{ $i }}">
                                <span>{{ $item['label'] }}</span>
                                <x-icon name="chevron" class="c-primary-nav-desktop__chevron" />
                            </button>

                            <div id="desktop-dropdown-{{ $i }}" class="c-primary-nav-desktop__dropdown"
                                hidden>
                                <div class="o-container">
                                    @include('sections.primary-nav-desktop-columns', ['groups' => $item['children'], 'idPrefix' => 'desktop-nav-group-' . $i])
                                </div>
                            </div>
                        @else
                            <a href="{{ $item['href'] }}"
                                class="c-primary-nav-desktop__link"><span>{{ $item['label'] }}</span></a>
                        @endif
                    </li>
                @endforeach

                {{-- Overflow menu: resources/js/primary-nav.js hides the
                     trailing top-level items that don't fit the bar (this li shows
                     only then) and reveals their matching section below. Every item
                     is rendered here up front so the JS only toggles [hidden]. --}}
                <li class="c-primary-nav-desktop__item c-primary-nav-desktop__item--more" hidden>
                    <button type="button" class="c-primary-nav-desktop__more" aria-expanded="false"
                        aria-controls="desktop-dropdown-overflow" aria-label="{{ __('More', 'sage') }}">
                        <x-icon name="menu" class="c-primary-nav-desktop__more-icon-menu" />
                        <x-icon name="close" class="c-primary-nav-desktop__more-icon-close" />
                    </button>

                    <div id="desktop-dropdown-overflow"
                        class="c-primary-nav-desktop__dropdown c-primary-nav-desktop__dropdown--overflow" hidden>
                        <div class="o-container">
                            @foreach ($items as $i => $item)
                                <div class="c-primary-nav-desktop__overflow-section" data-overflow-index="{{ $i }}" hidden>
                                    @if (!empty($item['children']))
                                        <p class="c-primary-nav-desktop__overflow-title">{{ $item['label'] }}</p>
                                        @include('sections.primary-nav-desktop-columns', ['groups' => $item['children'], 'idPrefix' => 'desktop-overflow-group-' . $i])
                                    @else
                                        <a href="{{ $item['href'] }}"
                                            class="c-primary-nav-desktop__overflow-title c-primary-nav-desktop__overflow-link"><span>{{ $item['label'] }}</span></a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </li>

                <li class="c-primary-nav-desktop__item">
                    {{-- Replaces the list with the search form below (Figma "Desktop | Search") --}}
                    <button type="button" class="c-primary-nav-desktop__search-toggle" aria-expanded="false"
                        aria-controls="desktop-search" aria-label="Search">
                        <x-icon name="search" />
                    </button>
                </li>
            </ul>

            <form id="desktop-search" role="search" aria-label="{{ __('Search this site', 'sage') }}" method="get" action="{{ home_url('/') }}"
                class="c-primary-nav-desktop__search" hidden>
                <div class="c-primary-nav-desktop__search-field">
                    <input type="search" name="s" class="c-primary-nav-desktop__search-input"
                        placeholder="Search for a keyword" aria-label="Search for a keyword">
                    <button type="submit" class="c-primary-nav-desktop__search-submit">
                        <x-icon name="search" />
                        <span>Search</span>
                    </button>
                </div>

                <button type="button" class="c-primary-nav-desktop__search-close" data-search-close>Close</button>
            </form>
        </div>
    </nav>
</div>
