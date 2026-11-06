@props([
  'text' => null,
  'type' => 'info', // info | success | warning | error
])

<div role="note" {{ $attributes->class(['c-callout-text', 'u-callout', "c-callout-text--{$type}" => $type !== 'info']) }}>
  <x-icon :name="match ($type) { 'success' => 'circle-check', 'warning', 'error' => 'alert', default => 'info' }" class="c-callout-text__icon" />
  <div class="c-callout-text__text u-rich-text">{!! $text ?? $slot !!}</div>
</div>
