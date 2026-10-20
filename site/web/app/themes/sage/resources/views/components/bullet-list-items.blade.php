{{-- One level of <x-bullet-list>; includes itself for nested items. --}}
<{{ $ordered ? 'ol' : 'ul' }} class="c-bullet-list__list">
  @foreach ($items as $item)
    {{-- Bold, italic and links allowed (e.g. migrated "<strong>Arnside Road:</strong> …"); anything else is stripped. --}}
    <li class="c-bullet-list__item">
      {!! wp_kses($item['text'], ['strong' => [], 'b' => [], 'em' => [], 'i' => [], 'a' => ['href' => [], 'target' => [], 'rel' => []]]) !!}
      @if ($item['children'])
        @include('components.bullet-list-items', ['items' => $item['children'], 'ordered' => $ordered])
      @endif
    </li>
  @endforeach
</{{ $ordered ? 'ol' : 'ul' }}>
