@props([
  'heading' => null,
  'headingClass' => null,
  'items' => [],
  'ordered' => false,
])

{{-- Items: [{text, level?}] — level (0–2) nests an item under the one above (App\Support\ListItems). --}}
<div {{ $attributes->class(['c-bullet-list', 'c-bullet-list--ordered' => $ordered]) }}>
  @if ($heading)
    <h3 @class(['c-bullet-list__heading', $headingClass])>{{ $heading }}</h3>
  @endif

  @include('components.bullet-list-items', ['items' => App\Support\ListItems::nest($items), 'ordered' => $ordered])
</div>
