@props([
  'items' => [],
])

<div {{ $attributes->class(['c-media-gallery__grid']) }}>
  @foreach ($items as $item)
    @php
      $src = $item['type'] === 'video' ? ($item['videoUrl'] ?? '') : ($item['thumbnail']['url'] ?? '');
      $label = ($item['type'] === 'video' ? __('Play', 'sage') : __('View', 'sage')).' '.$item['alt'];
    @endphp

    <button
      type="button"
      class="c-media-gallery__tile"
      style="--column-span: {{ $item['columnSpan'] }}"
      data-media-gallery-item
      data-type="{{ $item['type'] }}"
      data-src="{{ $src }}"
      data-poster="{{ $item['thumbnail']['url'] ?? '' }}"
      aria-label="{{ $label }}"
    >
      <x-media-thumbnail :type="$item['type']" :thumbnail="$item['thumbnail']" />
    </button>
  @endforeach
</div>
