@props([
  'text' => null,
])

<div role="note" {{ $attributes->class(['c-callout-text', 'u-callout']) }}>
  <x-icon name="info" class="c-callout-text__icon" />
  <div class="c-callout-text__text u-rich-text">{!! $text ?? $slot !!}</div>
</div>
