{{-- Mobile full-screen nav overlay (Figma "Mobile | Menu level 1/2") —
     L2 headings are inert, L3 items are the real links. --}}
<div id="primary-menu" class="c-primary-nav" hidden>
  <div class="c-primary-nav__panel" data-panel="root">
    <ul class="c-primary-nav__list" role="list">
      @foreach ($items as $i => $item)
        <li class="c-primary-nav__item">
          @if (! empty($item['children']))
            <button type="button" class="c-primary-nav__link c-primary-nav__link--drill" data-open-panel="l1-{{ $i }}">
              <span>{{ $item['label'] }}</span>
              <x-icon name="chevron" class="c-primary-nav__chevron" />
            </button>
          @else
            <a href="{{ $item['href'] }}" class="c-primary-nav__link"><span>{{ $item['label'] }}</span></a>
          @endif
        </li>
      @endforeach
    </ul>

    @include('partials.primary-nav-panel-footer', ['cta' => $cta, 'socialLinks' => $socialLinks])
  </div>

  @foreach ($items as $i => $item)
    @if (! empty($item['children']))
      <div class="c-primary-nav__panel" data-panel="l1-{{ $i }}" hidden>
        <button type="button" class="c-primary-nav__back" data-back-panel>
          <x-icon name="arrow-right" class="c-primary-nav__back-icon" />
          Back
        </button>

        <div class="c-primary-nav__groups">
          @foreach ($item['children'] as $group)
            <div class="c-primary-nav__group">
              <p class="c-primary-nav__heading">{{ $group['label'] }}</p>

              @if (! empty($group['children']))
                <ul class="c-primary-nav__list c-primary-nav__list--sub" role="list">
                  @foreach ($group['children'] as $link)
                    <li>
                      <a href="{{ $link['href'] }}" class="c-primary-nav__link c-primary-nav__link--sub">
                        <span>{{ $link['label'] }}</span>
                        <x-icon name="chevron" class="c-primary-nav__chevron" />
                      </a>
                    </li>
                  @endforeach
                </ul>
              @endif
            </div>
          @endforeach
        </div>

        @include('partials.primary-nav-panel-footer', ['cta' => $cta, 'socialLinks' => $socialLinks])
      </div>
    @endif
  @endforeach
</div>

{{-- Desktop bar + dropdown mega-menu (Figma "Desktop" / "Desktop | Dropdown").
     Same $items/content model as the mobile overlay, laid out as columns.
     Each dropdown lives inside its trigger's own <li> so Tab order flows
     into it naturally — position:absolute still finds .c-primary-nav-desktop
     as its containing block regardless of nesting depth. --}}
<div class="c-primary-nav-desktop">
    <nav class="c-primary-nav-desktop__bar" aria-label="{{ wp_get_nav_menu_name('primary_navigation') ?: 'Primary' }}">
        <div class="o-container">
            <ul class="c-primary-nav-desktop__list" role="list">
                @foreach ($items as $i => $item)
                    <li class="c-primary-nav-desktop__item">
                        @if (! empty($item['children']))
                            <button type="button" class="c-primary-nav-desktop__link" aria-expanded="false"
                                aria-controls="desktop-dropdown-{{ $i }}">
                                <span>{{ $item['label'] }}</span>
                                <x-icon name="chevron" class="c-primary-nav-desktop__chevron" />
                            </button>

                            <div id="desktop-dropdown-{{ $i }}" class="c-primary-nav-desktop__dropdown" hidden>
                                <div class="o-container">
                                    <div class="c-primary-nav-desktop__columns">
                                        @foreach ($item['children'] as $group)
                                            <div class="c-primary-nav-desktop__column">
                                                <p class="c-primary-nav-desktop__heading">{{ $group['label'] }}</p>

                                                @if (! empty($group['children']))
                                                    <ul class="c-primary-nav-desktop__sublist" role="list">
                                                        @foreach ($group['children'] as $link)
                                                            <li>
                                                                <a href="{{ $link['href'] }}" class="c-primary-nav-desktop__sublink">
                                                                    <span>{{ $link['label'] }}</span>
                                                                    <x-icon name="chevron" class="c-primary-nav-desktop__chevron c-primary-nav-desktop__chevron--sub" />
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
                            <a href="{{ $item['href'] }}" class="c-primary-nav-desktop__link"><span>{{ $item['label'] }}</span></a>
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

            <form id="desktop-search" role="search" aria-label="Site" method="get" action="{{ home_url('/') }}"
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
