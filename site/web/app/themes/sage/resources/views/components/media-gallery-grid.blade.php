@props([
  'items' => [],
  'isEditorPreview' => false,
])

<div {{ $attributes->class(['c-media-gallery__grid']) }}>
  @foreach ($items as $item)
    @php
      $src = $item['type'] === 'video' ? ($item['videoUrl'] ?? '') : ($item['thumbnail']['url'] ?? '');
      $label = ($item['type'] === 'video' ? __('Play', 'sage') : __('View', 'sage')).' '.$item['alt'];
    @endphp

    @if ($isEditorPreview)
      {{-- No lightbox in the real wp-admin block editor canvas — a native
           <button> here would intercept the click before it reaches
           Gutenberg's own "select this block" handling, making the block
           unselectable by clicking its content. --}}
      <div class="c-media-gallery__tile" style="--column-span: {{ $item['columnSpan'] }}">
        <x-media-thumbnail :type="$item['type']" :thumbnail="$item['thumbnail']" :video-url="$item['videoUrl']" :alt="$item['alt']" />
      </div>
    @else
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
        <x-media-thumbnail :type="$item['type']" :thumbnail="$item['thumbnail']" :video-url="$item['videoUrl']" />
      </button>
    @endif
  @endforeach
</div>
