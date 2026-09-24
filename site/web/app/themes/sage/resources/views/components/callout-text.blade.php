@props([
  'text' => null,
])

<p {{ $attributes->class(['c-callout-text', 'u-callout']) }}>
  <x-icon name="info" class="c-callout-text__icon" />
  <span class="c-callout-text__text">{{ $text ?? $slot }}</span>
</p>
