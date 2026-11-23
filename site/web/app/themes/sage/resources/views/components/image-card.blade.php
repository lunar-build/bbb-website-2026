@props(['image', 'link'])

<a
  {{ $attributes->class(['c-image-card__card']) }}
  href="{{ $link['url'] }}"
  @if (($link['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif
>
  <x-image class="c-image-card__image" :image="$image" size="medium_large" />

  <span class="c-image-card__caption">
    <span class="c-image-card__title">{{ $link['title'] ?: __('Find out more', 'sage') }}</span>
    <x-icon name="arrow-right" class="c-image-card__icon" />
  </span>
</a>
