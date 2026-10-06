@props([
  'heading' => null,
  'items' => [],
])

<div {{ $attributes->class(['c-bullet-list']) }}>
  @if ($heading)
    <h3 class="c-bullet-list__heading">{{ $heading }}</h3>
  @endif

  <ul class="c-bullet-list__list">
    @foreach ($items as $item)
      <li class="c-bullet-list__item">{{ $item['text'] }}</li>
    @endforeach
  </ul>
</div>
