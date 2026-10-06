@php
    $placeholder = ['url' => \Illuminate\Support\Facades\Vite::asset('resources/images/placeholder/pattern-placeholder.svg'), 'alt' => ''];
@endphp

{{-- Wrapped in a real <wa-carousel> here (not just bare cards) so the
     pagination dots / arrows / swipe / loop behaviour this component is
     designed to sit inside is actually visible on the pattern library page,
     not just its static appearance. --}}
<wa-carousel class="c-testimonial-carousel__carousel" navigation pagination loop mouse-dragging>
    <wa-carousel-item class="c-testimonial-carousel__item">
        <x-testimonial-card
            :photo="$placeholder"
            name="Rachel"
            quote="<p>I ride to avoid the cost and stress of driving and parking plus getting some extra fitness in.</p>"
            :link="['title' => 'Read more', 'url' => '#', 'target' => '']"
        />
    </wa-carousel-item>

    <wa-carousel-item class="c-testimonial-carousel__item">
        <x-testimonial-card
            :photo="$placeholder"
            name="Franciska"
            quote="<p>It keeps me healthy. I don't feel tired like I used to. I used to get very bad headaches but I don't get them anymore since I started cycling.</p>"
            :link="['title' => 'Read more', 'url' => '#', 'target' => '']"
        />
    </wa-carousel-item>

    <wa-carousel-item class="c-testimonial-carousel__item">
        <x-testimonial-card
            :photo="$placeholder"
            name="Tom"
            quote="<p>Cycling to work means I never have to worry about parking, and I arrive feeling ready for the day.</p>"
            :link="['title' => 'Read more', 'url' => '#', 'target' => '']"
        />
    </wa-carousel-item>

    <x-icon name="arrow-right" slot="previous-icon" class="c-testimonial-carousel__nav-icon c-testimonial-carousel__nav-icon--prev" />
    <x-icon name="arrow-right" slot="next-icon" class="c-testimonial-carousel__nav-icon" />
</wa-carousel>
