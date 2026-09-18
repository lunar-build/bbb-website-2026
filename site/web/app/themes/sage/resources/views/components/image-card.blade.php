@props(['image', 'link'])

<a
  {{ $attributes->class(['c-image-card__card']) }}
  href="{{ $link['url'] }}"
  @if (($link['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif
>
  <img class="c-image-card__image" src="{{ $image['url'] }}" alt="{{ $image['alt'] ?? '' }}">

  <span class="c-image-card__caption">
    <span class="c-image-card__title">{{ $link['title'] }}</span>
    <x-icon name="arrow-right" class="c-image-card__icon" />
  </span>
</a>
