<section {{ $attributes->class(['c-cycle-route-card']) }}>

@if ($routeName)
<div class="o-container">
  <article class="c-cycle-route-card__card c-cycle-route-card--{{ $difficulty }}">
    <div class="c-cycle-route-card__image">
      @if ($image)
        <img src="{{ $image['url'] }}" alt="{{ $image['alt'] ?? '' }}">
      @endif
    </div>

    <div class="c-cycle-route-card__banner">
      <h2 class="c-cycle-route-card__route-name">{{ $routeName }}</h2>
    </div>

    <dl class="c-cycle-route-card__info">
      <div class="c-cycle-route-card__info-row">
        <dt class="c-cycle-route-card__info-label">{{ __('Time needed', 'sage') }}</dt>
        <dd class="c-cycle-route-card__info-value">{{ $timeNeeded }}</dd>
      </div>

      <div class="c-cycle-route-card__info-row">
        <dt class="c-cycle-route-card__info-label">{{ __('Distance', 'sage') }}</dt>
        <dd class="c-cycle-route-card__info-value">{{ $distance }}</dd>
      </div>

      <div class="c-cycle-route-card__info-row">
        <dt class="c-cycle-route-card__info-label">{{ __('Difficulty', 'sage') }}</dt>
        <dd class="c-cycle-route-card__badge">{{ ucfirst($difficulty) }}</dd>
      </div>
    </dl>

    <a
      class="c-cycle-route-card__cta"
      href="{{ $link['url'] }}"
      @if (($link['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif
    >
      {{ $link['title'] }}
      <x-icon name="arrow-right" />
      @if (($link['target'] ?? '') === '_blank')
        <span class="u-sr-only">({{ __('opens in a new tab', 'sage') }})</span>
      @endif
    </a>
  </article>
</div>
@endif

</section>
