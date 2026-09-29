<section {{ $attributes->class(['c-testimonial-carousel']) }}>

  <div class="o-container">
    <wa-carousel data-testimonial-carousel class="c-testimonial-carousel__carousel" navigation pagination loop mouse-dragging>
      @foreach ($cards as $card)
        <wa-carousel-item class="c-testimonial-carousel__item">
          <x-testimonial-card
            :photo="$card['photo']"
            :name="$card['name']"
            :quote="$card['quote']"
            :link="$card['link']"
          />
        </wa-carousel-item>
      @endforeach

      <x-icon name="arrow-right" slot="previous-icon" class="c-testimonial-carousel__nav-icon c-testimonial-carousel__nav-icon--prev" />
      <x-icon name="arrow-right" slot="next-icon" class="c-testimonial-carousel__nav-icon" />
    </wa-carousel>
  </div>

</section>
