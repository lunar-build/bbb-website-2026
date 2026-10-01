@props([
  'type' => 'image',
  'thumbnail' => null,
  'alt' => '',
])

<span {{ $attributes->class(['c-media-thumbnail']) }}>
  <img class="c-media-thumbnail__image" src="{{ $thumbnail['url'] ?? '' }}" alt="{{ $alt }}">

  @if ($type === 'video')
    <x-icon name="video-play" class="c-media-thumbnail__play" />
  @endif
</span>
