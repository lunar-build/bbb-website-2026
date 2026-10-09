<section {{ $attributes->class(['c-video-block']) }}>

<div class="o-container">
  @if ($video['url'] ?? null)
    <div class="c-video-block__media" data-video-block>
      {{-- Native controls are the no-JS fallback; video-block.js swaps in the custom toggle. --}}
      <video
        class="c-video-block__video"
        data-video-block-video
        controls
        playsinline
        preload="metadata"
        aria-label="{{ $heading['text'] }}"
        @if ($poster['url'] ?? null) poster="{{ $poster['url'] }}" @endif
      >
        <source src="{{ $video['url'] }}" type="video/mp4">
      </video>

      <button
        type="button"
        class="c-video-block__toggle"
        data-video-block-toggle
        data-video-block-label="{{ $heading['text'] }}"
        aria-label="{{ sprintf(__('Play %s', 'sage'), $heading['text']) }}"
        hidden
      >
        <x-icon name="video-play" class="c-video-block__toggle-icon" data-video-block-icon="play" />
        <x-icon name="video-pause" class="c-video-block__toggle-icon" data-video-block-icon="pause" hidden />
      </button>
    </div>
  @elseif ($block->preview)
    <div class="c-video-block__placeholder">
      {{ __('Add a video file…', 'sage') }}
    </div>
  @endif

  <div class="c-video-block__transcript">
    <x-heading :level="$heading['level']" :style="$heading['style']" class="c-video-block__title">
      {{ $heading['text'] }}
    </x-heading>

    @if ($transcript['url'])
      @php $fileSizeId = 'c-video-block__file-size-'.uniqid(); @endphp

      <x-download-link
        class="c-video-block__download"
        :url="$transcript['url']"
        :label="$transcript['label']"
        :file-size="$transcript['file_size']"
        :id="$fileSizeId"
      />
    @endif
  </div>
</div>

</section>
