@props([
  'heading' => null,
  'headingClass' => null,
  'items' => [],
])

<div {{ $attributes->class(['c-bullet-list']) }}>
  @if ($heading)
    <h3 @class(['c-bullet-list__heading', $headingClass])>{{ $heading }}</h3>
  @endif

  <ul class="c-bullet-list__list">
    @foreach ($items as $item)
      {{-- Bold, italic and links allowed (e.g. migrated "<strong>Arnside Road:</strong> …"); anything else is stripped. --}}
      <li class="c-bullet-list__item">{!! wp_kses($item['text'], ['strong' => [], 'b' => [], 'em' => [], 'i' => [], 'a' => ['href' => [], 'target' => [], 'rel' => []]]) !!}</li>
    @endforeach
  </ul>
</div>
