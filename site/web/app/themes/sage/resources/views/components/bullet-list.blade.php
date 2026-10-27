@props([
  'heading' => null,
  'headingClass' => null,
  'headingLevel' => 'h3',
  'items' => [],
  'ordered' => false,
])

{{-- Items: [{text, level?}] — level (0–2) nests an item under the one above (App\Support\ListItems). --}}
<div {{ $attributes->class(['c-bullet-list', 'c-bullet-list--ordered' => $ordered]) }}>
  @if ($heading)
    {{-- Level is semantic only: it always looks like an H3 unless headingClass says otherwise. --}}
    <{{ $headingLevel }} @class(['c-bullet-list__heading', $headingClass ?? 'u-heading-3'])>{{ $heading }}</{{ $headingLevel }}>
  @endif

  @include('components.bullet-list-items', ['items' => App\Support\ListItems::nest($items), 'ordered' => $ordered])
</div>
