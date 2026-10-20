{{--
  Reusable pull-quote fragment (also hardcodable outside the Quote block).
  $size: 'large' (default) | 'standard' — Figma Short/Long variants' scale.
  $layout: 'default' | 'centered' — see _quote.scss's .c-quote--centered.
  Colour overrides via --c-quote-color/--c-quote-attribution-color.
  Attribution hides when $attributionName is empty; $attributionRole needs both.
  $image: optional ACF image array, shown as a circular headshot beside the attribution.
--}}

@props([
    'size' => 'large',
    'layout' => 'default',
    'attributionName' => null,
    'attributionRole' => null,
    'image' => null,
])

<div {{ $attributes->class(['c-quote', 'c-quote--'.$size, 'c-quote--'.$layout]) }}>
    <x-icon name="quote-mark" class="c-quote__mark" />

    <blockquote class="c-quote__body">
        {{ $slot }}
    </blockquote>

    @if ($attributionName)
        <cite class="c-quote__attribution">
            @if (! empty($image['url']))
                {{-- Decorative: the name beside it already identifies the person. --}}
                <img class="c-quote__attribution-image" src="{{ $image['sizes']['thumbnail'] ?? $image['url'] }}" alt="">
            @endif
            <span class="c-quote__attribution-text">
                <span class="c-quote__attribution-name">{{ $attributionName }}</span>
                @if ($attributionRole)
                    <span class="c-quote__attribution-role">{{ $attributionRole }}</span>
                @endif
            </span>
        </cite>
    @endif
</div>
