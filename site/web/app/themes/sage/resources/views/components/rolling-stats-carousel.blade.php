@props(['slides' => []])

<div {{ $attributes->class(['c-rolling-stats-carousel']) }} data-rolling-stats-carousel>
  <wa-carousel navigation pagination loop mouse-dragging class="c-rolling-stats-carousel__carousel">
    @foreach ($slides as $slide)
      <wa-carousel-item class="c-rolling-stats-carousel__item">
        <div class="c-rolling-stats-carousel__content">
          <div class="c-rolling-stats-carousel__text">
            @if (! empty($slide['prefix']))
              <p class="c-rolling-stats-carousel__prefix u-heading-4">{{ $slide['prefix'] }}</p>
            @endif

            {{-- Presentational only — the count-up animates this span's
            text, but it's aria-hidden since a live-updating number is
            noisy/unreliable for screen readers. The real, final value is
            given once, in full, via the sr-only sentence below. --}}
            <p class="c-rolling-stats-carousel__stat" aria-hidden="true">
              <span
                class="c-rolling-stats-carousel__value u-stat-value"
                data-rolling-stats-value
                data-target="{{ $slide['value'] }}"
                data-decimals="{{ $slide['decimals'] }}"
              >0</span>
              @if (! empty($slide['unit']))
                <span class="c-rolling-stats-carousel__unit u-stat-value">{{ $slide['unit'] }}</span>
              @endif
            </p>

            <span class="u-sr-only">{{ $slide['accessibleText'] }}</span>

            @if (! empty($slide['caption']))
              <p class="c-rolling-stats-carousel__caption u-heading-4">{{ $slide['caption'] }}</p>
            @endif
          </div>

          @if (! empty($slide['image']['url']))
            <img
              class="c-rolling-stats-carousel__illustration"
              src="{{ $slide['image']['url'] }}"
              alt="{{ $slide['image']['alt'] ?? '' }}"
              loading="lazy"
            />
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
</div>
