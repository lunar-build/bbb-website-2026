<section {{ $attributes->class(['c-testimonial-carousel', 'o-full-bleed-bg']) }}>

@if ($cards)
  <div class="o-container">
    <wa-carousel data-testimonial-carousel class="c-testimonial-carousel__carousel" navigation pagination loop mouse-dragging>
      @foreach ($cards as $card)
        <wa-carousel-item class="c-testimonial-carousel__item">
          <x-testimonial-card
            :type="$card['type']"
            :photo="$card['photo']"
            :name="$card['name']"
            :info="$card['info']"
            :quote="$card['quote']"
            :body="$card['body']"
            :link="$card['link']"
          />
        </wa-carousel-item>
      @endforeach

      <x-icon name="arrow-right" slot="previous-icon" class="c-testimonial-carousel__nav-icon c-testimonial-carousel__nav-icon--prev" />
      <x-icon name="arrow-right" slot="next-icon" class="c-testimonial-carousel__nav-icon" />
    </wa-carousel>
  </div>
@endif

</section>
