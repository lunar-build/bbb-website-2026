{{--
  Single carousel card: photo, name/title heading, then either a pull-quote
  (type "testimonial", via <x-quote layout="centered">, no attribution/cite
  line) or plain text (type "content"), and a CTA pill. Figma node 7:2013/7:2022. Used inside a <wa-carousel-item> by the
  Testimonial Card Carousel block (app/Blocks/TestimonialCardCarousel.php);
  also renders standalone here for the pattern library "Components" section.
--}}

@props([
    'type' => 'testimonial',
    'photo',
    'name',
    'info' => null,
    'quote' => null,
    'body' => null,
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

        @if ($info)
            <p class="c-testimonial-card__info u-body-regular">{{ $info }}</p>
        @endif

        @if ($type === 'content')
            @if ($body)
                <x-copy class="c-testimonial-card__body">{!! $body !!}</x-copy>
            @endif
        @else
            <x-quote layout="centered" size="standard" class="c-testimonial-card__quote">
                {!! $quote !!}
            </x-quote>
        @endif

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
