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
                      {{-- 0-9 x3 so the reel visibly spins before landing on the target set. --}}
                      @for ($set = 0; $set < 3; $set++)
                        @for ($n = 0; $n <= 9; $n++)
                          <span class="c-rolling-stats-carousel__reel-digit">{{ $n }}</span>
                        @endfor
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
