<section {{ $attributes->class(['c-instagram-feed', 'o-full-bleed-bg']) }}>
  <div class="o-container">
    @if (! empty($heading['text']) || ! empty($intro['text']))
      <div class="c-instagram-feed__text">
        @if (! empty($heading['text']))
          <x-heading :level="$heading['level']" :style="$heading['style']" class="c-instagram-feed__heading">
            {{ $heading['text'] }}
          </x-heading>
        @endif

        @if (! empty($intro['text']))
          <x-copy :style="$intro['style']" class="c-instagram-feed__intro">
            {!! $intro['text'] !!}
          </x-copy>
        @endif
      </div>
    @endif

    @if ($feedShortcode)
      <div class="c-instagram-feed__feed">
        {!! do_shortcode($feedShortcode) !!}
      </div>
    @endif
  </div>
</section>
