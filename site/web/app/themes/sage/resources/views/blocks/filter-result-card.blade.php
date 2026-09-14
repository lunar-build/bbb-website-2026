@unless ($block->preview)
  <section {{ $attributes->class(['c-filter-result-card']) }}>
@endunless

<div class="o-container">
  <div class="c-filter-result-card__card">
    <h4 class="c-filter-result-card__name">{{ $name }}</h4>

    @if (! empty($contactInfo))
      <hr class="c-filter-result-card__rule">

      <ul class="c-filter-result-card__contact-info">
        @foreach ($contactInfo as $row)
          <li class="c-filter-result-card__contact-item">
            <wa-icon name="{{ $row['icon'] }}" class="c-filter-result-card__icon"></wa-icon>
            <span class="c-filter-result-card__contact-text">{{ $row['text'] }}</span>
          </li>
        @endforeach
      </ul>
    @endif

    @if (! empty($services))
      <hr class="c-filter-result-card__rule">

      <ul class="c-filter-result-card__services">
        @foreach ($services as $row)
          <li class="c-filter-result-card__service">
            <wa-icon name="{{ $row['icon'] }}" class="c-filter-result-card__icon"></wa-icon>
            <span class="c-filter-result-card__service-text">{{ $row['text'] }}</span>
          </li>
        @endforeach
      </ul>
    @endif

    @if ($description)
      <hr class="c-filter-result-card__rule">

      <p class="c-filter-result-card__description">{{ $description }}</p>
    @endif

    <a
      class="c-filter-result-card__cta"
      href="{{ $link['url'] }}"
      @if (($link['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif
    >
      {{ $link['title'] }}
      <x-icon name="arrow-right" />
    </a>
  </div>
</div>

@unless ($block->preview)
  </section>
@endunless
