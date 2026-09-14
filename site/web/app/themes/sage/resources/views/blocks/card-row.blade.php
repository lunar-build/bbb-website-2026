{{-- The type modifier lives on the .o-container div below, not this
     <section> — the <section> is skipped when $block->preview is true (both
     the real Gutenberg editor preview and the pattern-library page render
     with preview=true), which would make every type-specific rule invisible
     on /pattern-library if it depended on this wrapper (see CtaBanner's own
     note on the same $block->preview quirk). --}}
@unless ($block->preview)
  <section {{ $attributes->class(['c-card-row']) }}>
@endunless

<div class="o-container c-card-row--{{ $type }}">
  <h2 class="c-card-row__heading">
    <span class="c-card-row__heading-highlight">{{ $headingHighlight }}</span>
    {{ $heading }}
  </h2>

  @if ($intro)
    <p class="c-card-row__intro u-body-large">{{ $intro }}</p>
  @endif

  <div class="c-card-row__cards">
    @if ($type === 'news')
      @foreach ($newsCards as $card)
        <a class="c-card-row__news-card" href="{{ $card['link']['url'] }}" @if (($card['link']['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif>
          <img class="c-card-row__news-image" src="{{ $card['image']['url'] }}" alt="{{ $card['image']['alt'] ?? '' }}">

          <span class="c-card-row__news-tag">{{ $card['tag'] }}</span>
          <span class="c-card-row__news-title">{{ $card['title'] }}</span>
          <span class="c-card-row__news-cta">{{ $card['link']['title'] }}</span>
        </a>
      @endforeach
    @elseif ($type === 'route')
      @foreach ($routeCards as $card)
        <div class="c-cycle-route-card__card c-cycle-route-card--{{ $card['difficulty'] }}">
          <div class="c-cycle-route-card__image">
            <img src="{{ $card['image']['url'] }}" alt="{{ $card['image']['alt'] ?? '' }}">
          </div>

          <div class="c-cycle-route-card__banner">
            <span class="c-cycle-route-card__route-name">{{ $card['route_name'] }}</span>
          </div>

          <div class="c-cycle-route-card__info">
            <div class="c-cycle-route-card__info-row">
              <span class="c-cycle-route-card__info-label">{{ __('Time needed', 'sage') }}</span>
              <span class="c-cycle-route-card__info-value">{{ $card['time_needed'] }}</span>
            </div>

            <div class="c-cycle-route-card__info-row">
              <span class="c-cycle-route-card__info-label">{{ __('Distance', 'sage') }}</span>
              <span class="c-cycle-route-card__info-value">{{ $card['distance'] }}</span>
            </div>

            <div class="c-cycle-route-card__info-row">
              <span class="c-cycle-route-card__info-label">{{ __('Difficulty', 'sage') }}</span>
              <span class="c-cycle-route-card__badge">{{ ucfirst($card['difficulty']) }}</span>
            </div>
          </div>

          <a
            class="c-cycle-route-card__cta"
            href="{{ $card['link']['url'] }}"
            @if (($card['link']['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif
          >
            {{ $card['link']['title'] }}
            <x-icon name="arrow-right" />
          </a>
        </div>
      @endforeach
    @else
      @foreach ($linkCards as $card)
        <div class="c-card-row__link-card">
          <img class="c-card-row__link-image" src="{{ $card['image']['url'] }}" alt="{{ $card['image']['alt'] ?? '' }}">

          <span class="c-card-row__link-title">{{ $card['title'] }}</span>
          <p class="c-card-row__link-body">{{ $card['body'] }}</p>

          <a
            class="c-card-row__link-cta"
            href="{{ $card['link']['url'] }}"
            @if (($card['link']['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif
          >
            {{ $card['link']['title'] }}
            <x-icon name="arrow-right" />
          </a>
        </div>
      @endforeach
    @endif
  </div>

  @if ($type === 'route' && ! empty($areaLinks))
    <div class="c-card-row__area-links">
      @foreach ($areaLinks as $row)
        <a
          class="c-card-row__area-link"
          href="{{ $row['link']['url'] }}"
          @if (($row['link']['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif
        >
          {{ $row['link']['title'] }}
          <x-icon name="arrow-right" />
        </a>
      @endforeach
    </div>
  @endif

  @if ($browseAllLink)
    <a
      class="c-card-row__browse-all"
      href="{{ $browseAllLink['url'] }}"
      @if (($browseAllLink['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif
    >
      {{ $browseAllLink['title'] }}
      <x-icon name="arrow-right" />
    </a>
  @endif
</div>

@unless ($block->preview)
  </section>
@endunless
