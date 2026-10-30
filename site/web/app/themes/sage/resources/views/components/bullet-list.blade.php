@props([
  'heading' => null,
  'headingClass' => null,
  'headingLevel' => 'h3',
  'headingStyle' => 'h3',
  'items' => [],
  'ordered' => false,
])

{{-- Items: [{text, level?}] — level (0–2) nests an item under the one above (App\Support\ListItems). --}}
<div {{ $attributes->class(['c-bullet-list', 'c-bullet-list--ordered' => $ordered]) }}>
  @if ($heading)
    {{-- headingClass, when given, overrides headingStyle's size. --}}
    <x-heading :level="$headingLevel" :style="$headingClass ? 'match' : $headingStyle" :class="trim('c-bullet-list__heading '.$headingClass)">{{ $heading }}</x-heading>
  @endif

  @include('components.bullet-list-items', ['items' => App\Support\ListItems::nest($items), 'ordered' => $ordered])
</div>
