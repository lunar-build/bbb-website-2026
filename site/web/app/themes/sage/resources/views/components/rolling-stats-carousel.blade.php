@props(['slides' => [], 'imageLeft' => null, 'imageRight' => null])

<div {{ $attributes->class(['c-rolling-stats-carousel']) }} data-rolling-stats-carousel>
  @if ($imageLeft)
    <img
      class="c-rolling-stats-carousel__illustration c-rolling-stats-carousel__illustration--left"
      src="{{ $imageLeft }}"
      alt=""
      loading="lazy"
    />
  @endif

  <wa-carousel navigation pagination loop mouse-dragging class="c-rolling-stats-carousel__carousel">
    @foreach ($slides as $slide)
      <wa-carousel-item class="c-rolling-stats-carousel__item">
        <div class="c-rolling-stats-carousel__content">
          @if (($slide['type'] ?? 'stat') === 'statement')
            <p class="c-rolling-stats-carousel__statement">{!! $slide['statement'] !!}</p>
          @else
          @if (! empty($slide['prefix']))
            <p class="c-rolling-stats-carousel__prefix u-heading-4">{{ $slide['prefix'] }}</p>
          @endif

          {{-- aria-hidden: rolling digits are noisy for screen readers; real value is the sr-only sentence below. --}}
          <p class="c-rolling-stats-carousel__stat" aria-hidden="true">
            <span class="c-rolling-stats-carousel__value u-stat-value" data-rolling-stats-value>
              @foreach ($slide['digits'] as $digit)
                @if ($digit === '.')
                  <span class="c-rolling-stats-carousel__decimal">.</span>
                @else
                  <span class="c-rolling-stats-carousel__reel" data-rolling-stats-reel data-digit="{{ $digit }}">
                    <span class="c-rolling-stats-carousel__reel-track">
                      {{-- 0-9 then a trailing 0, so the roll from 9 back to 0 has a row to land on. --}}
                      @for ($n = 0; $n <= 10; $n++)
                        <span class="c-rolling-stats-carousel__reel-digit">{{ $n % 10 }}</span>
                      @endfor
                    </span>
                  </span>
                @endif
              @endforeach
            </span>
            @if (! empty($slide['unit']))
              <span class="c-rolling-stats-carousel__unit u-stat-value">{{ $slide['unit'] }}</span>
            @endif
          </p>

          <span class="u-sr-only">{{ $slide['accessibleText'] }}</span>

          @if (! empty($slide['caption']))
            <p class="c-rolling-stats-carousel__caption u-heading-4">{{ $slide['caption'] }}</p>
          @endif
          @endif

          @if (! empty($slide['source']))
            <p class="c-rolling-stats-carousel__source u-body-regular">{{ $slide['source'] }}</p>
          @endif

          @if (! empty($slide['link']['url']))
            <wa-button
              class="c-rolling-stats-carousel__cta"
              variant="brand"
              appearance="accent"
              pill
              with-end
              href="{{ $slide['link']['url'] }}"
              @if (($slide['link']['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif
            >
              {{ $slide['link']['title'] ?: __('Find out more', 'sage') }}
              <x-icon name="arrow-right" slot="end" />
            </wa-button>
          @endif
        </div>
      </wa-carousel-item>
    @endforeach

    <x-icon
      name="arrow-right"
      slot="previous-icon"
      class="c-rolling-stats-carousel__nav-icon c-rolling-stats-carousel__nav-icon--previous"
    />
    <x-icon
      name="arrow-right"
      slot="next-icon"
      class="c-rolling-stats-carousel__nav-icon c-rolling-stats-carousel__nav-icon--next"
    />
  </wa-carousel>

  @if ($imageRight)
    <img
      class="c-rolling-stats-carousel__illustration c-rolling-stats-carousel__illustration--right"
      src="{{ $imageRight }}"
      alt=""
      loading="lazy"
    />
  @endif
</div>
