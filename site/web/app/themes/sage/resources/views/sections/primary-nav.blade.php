{{-- Mobile-only full-screen nav overlay (Figma "Mobile | Menu level 1/2").
     Desktop still uses <lunar-nav> in header.blade.php for now — this is
     mobile only, per Phase 3's mobile-first scope. L1 items with children
     drill into their own level-2 panel (inert L2 group headings + real L3
     links, matching the mega-menu content model — not a recursive
     accordion). Panel switching/back/escape handled by primary-nav.js. --}}
<div id="primary-menu" class="c-primary-nav" hidden>
  <div class="c-primary-nav__panel" data-panel="root">
    <ul class="c-primary-nav__list">
      @foreach ($items as $i => $item)
        <li class="c-primary-nav__item">
          @if (! empty($item['children']))
            <button type="button" class="c-primary-nav__link c-primary-nav__link--drill" data-open-panel="l1-{{ $i }}">
              <span>{{ $item['label'] }}</span>
              <x-icon name="chevron" class="c-primary-nav__chevron" />
            </button>
          @else
            <a href="{{ $item['href'] }}" class="c-primary-nav__link">{{ $item['label'] }}</a>
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
                <ul class="c-primary-nav__list c-primary-nav__list--sub">
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
