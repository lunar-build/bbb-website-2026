{{--
  Single testimonial card: person photo, name heading, pull-quote (via
  <x-quote layout="centered">, no attribution/cite line), and a "Read more"
  CTA pill. Figma node 7:2013/7:2022. Used inside a <wa-carousel-item> by the
  Testimonial Card Carousel block (app/Blocks/TestimonialCardCarousel.php);
  also renders standalone here for the pattern library "Components" section.
--}}

@props([
    'photo',
    'name',
    'quote',
    'link' => null,
])

<div {{ $attributes->class(['c-testimonial-card']) }}>
    <div class="c-testimonial-card__photo">
        <img src="{{ $photo['url'] }}" alt="{{ $photo['alt'] ?? $name }}">
    </div>

    <div class="c-testimonial-card__content">
        <x-heading level="h3" style="match" class="c-testimonial-card__name">
            {{ $name }}
        </x-heading>

        <x-quote layout="centered" size="standard" class="c-testimonial-card__quote">
            {!! $quote !!}
        </x-quote>

        @if (! empty($link['url']))
            <wa-button
                class="c-testimonial-card__cta"
                variant="brand"
                appearance="accent"
                size="small"
                pill
                with-end
                href="{{ $link['url'] }}"
                @if (($link['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif
            >
                {{ $link['title'] ?: __('Read more', 'sage') }}
                <x-icon name="arrow-right" slot="end" />
            </wa-button>
        @endif
    </div>
</div>
