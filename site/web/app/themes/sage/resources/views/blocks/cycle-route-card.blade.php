<section {{ $attributes->class(['c-cycle-route-card']) }}>

<div class="o-container">
  <div class="c-cycle-route-card__card c-cycle-route-card--{{ $difficulty }}">
    <div class="c-cycle-route-card__image">
      @if ($image)
        <img src="{{ $image['url'] }}" alt="{{ $image['alt'] ?? '' }}">
      @endif
    </div>

    <div class="c-cycle-route-card__banner">
      <span class="c-cycle-route-card__route-name">{{ $routeName }}</span>
    </div>

    <div class="c-cycle-route-card__info">
      <div class="c-cycle-route-card__info-row">
        <span class="c-cycle-route-card__info-label">{{ __('Time needed', 'sage') }}</span>
        <span class="c-cycle-route-card__info-value">{{ $timeNeeded }}</span>
      </div>

      <div class="c-cycle-route-card__info-row">
        <span class="c-cycle-route-card__info-label">{{ __('Distance', 'sage') }}</span>
        <span class="c-cycle-route-card__info-value">{{ $distance }}</span>
      </div>

      <div class="c-cycle-route-card__info-row">
        <span class="c-cycle-route-card__info-label">{{ __('Difficulty', 'sage') }}</span>
        <span class="c-cycle-route-card__badge">{{ ucfirst($difficulty) }}</span>
      </div>
    </div>

    <a
      class="c-cycle-route-card__cta"
      href="{{ $link['url'] }}"
      @if (($link['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif
    >
      {{ $link['title'] }}
      <x-icon name="arrow-right" />
    </a>
  </div>
</div>

</section>
