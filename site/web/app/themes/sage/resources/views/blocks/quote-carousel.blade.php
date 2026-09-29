<section {{ $attributes->class(['c-quote-carousel']) }}>

  <div class="o-container">
    <wa-carousel
      class="c-quote-carousel__carousel c-quote-carousel__carousel--{{ $style }}"
      navigation
      pagination
      loop
    >
      @foreach ($slides as $slide)
        <wa-carousel-item class="c-quote-carousel__item">
          <x-quote :attribution-name="$slide['attribution'] ?? null" class="c-quote-carousel__quote">
            {{ $slide['quote_prefix'] }}<strong class="c-quote-carousel__stat">{{ $slide['stat'] }}</strong>{{ $slide['quote_suffix'] ?? '' }}
          </x-quote>
        </wa-carousel-item>
      @endforeach

      <span slot="previous-icon" class="c-quote-carousel__nav-icon c-quote-carousel__nav-icon--previous">
        <x-icon name="arrow-right" class="c-quote-carousel__nav-icon-svg" />
      </span>
      <span slot="next-icon" class="c-quote-carousel__nav-icon">
        <x-icon name="arrow-right" class="c-quote-carousel__nav-icon-svg" />
      </span>
    </wa-carousel>
  </div>

</section>
