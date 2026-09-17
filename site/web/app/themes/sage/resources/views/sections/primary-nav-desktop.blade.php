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
                    <li class="c-primary-nav-desktop__item">
                        @if (!empty($item['children']))
                            <button type="button" class="c-primary-nav-desktop__link" aria-expanded="false"
                                aria-controls="desktop-dropdown-{{ $i }}">
                                <span>{{ $item['label'] }}</span>
                                <x-icon name="chevron" class="c-primary-nav-desktop__chevron" />
                            </button>

                            <div id="desktop-dropdown-{{ $i }}" class="c-primary-nav-desktop__dropdown"
                                hidden>
                                <div class="o-container">
                                    <div class="c-primary-nav-desktop__columns">
                                        @foreach ($item['children'] as $group)
                                            <div class="c-primary-nav-desktop__column">
                                                <p class="c-primary-nav-desktop__heading">{{ $group['label'] }}</p>

                                                @if (!empty($group['children']))
                                                    <ul class="c-primary-nav-desktop__sublist" role="list">
                                                        @foreach ($group['children'] as $link)
                                                            <li>
                                                                <a href="{{ $link['href'] }}"
                                                                    class="c-primary-nav-desktop__sublink">
                                                                    <span>{{ $link['label'] }}</span>
                                                                    <x-icon name="chevron"
                                                                        class="c-primary-nav-desktop__chevron c-primary-nav-desktop__chevron--sub" />
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @else
                            <a href="{{ $item['href'] }}"
                                class="c-primary-nav-desktop__link"><span>{{ $item['label'] }}</span></a>
                        @endif
                    </li>
                @endforeach

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
