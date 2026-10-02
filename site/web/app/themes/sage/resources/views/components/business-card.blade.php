@props(['name', 'contactInfo' => [], 'services' => [], 'description' => null, 'link'])

<div {{ $attributes->class(['c-business-card__card']) }}>
  <h4 class="c-business-card__name">{{ $name }}</h4>

  @if (! empty($contactInfo))
    <hr class="c-business-card__rule">

    <ul class="c-business-card__contact-info">
      @foreach ($contactInfo as $row)
        <li class="c-business-card__contact-item">
          <wa-icon name="{{ $row['icon'] }}" class="c-business-card__icon"></wa-icon>
          @if ($row['href'])
            <a class="c-business-card__contact-text c-business-card__contact-link" href="{{ $row['href'] }}" @if ($row['icon'] === 'location-dot' || $row['icon'] === 'globe') target="_blank" rel="noopener" @endif>{{ $row['text'] }}</a>
          @else
            <span class="c-business-card__contact-text">{{ $row['text'] }}</span>
          @endif
        </li>
      @endforeach
    </ul>
  @endif

  @if (! empty($services))
    <hr class="c-business-card__rule">

    <ul class="c-business-card__services">
      @foreach ($services as $row)
        <li class="c-business-card__service">
          <wa-icon name="{{ $row['icon'] }}" class="c-business-card__icon"></wa-icon>
          <span class="c-business-card__service-text">{{ $row['text'] }}</span>
        </li>
      @endforeach
    </ul>
  @endif

  @if ($description)
    <hr class="c-business-card__rule">

    <p class="c-business-card__description">{{ $description }}</p>
  @endif

  <a
    class="c-business-card__cta"
    href="{{ $link['url'] }}"
    @if (($link['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif
  >
    {{ $link['title'] }}
    <x-icon name="arrow-right" />
  </a>
</div>
