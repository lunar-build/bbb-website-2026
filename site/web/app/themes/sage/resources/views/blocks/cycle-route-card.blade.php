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

    <dl class="c-cycle-route-card__info"
      aria-label="{{ sprintf(__('Details for %s', 'sage'), $routeName) }}">
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

    <x-link class="c-cycle-route-card__cta" :link="$link" />
  </article>
</div>
@endif

</section>
