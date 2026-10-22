@if ($character)
{{-- A div, not a <section>: purely decorative, with no content or heading, so it isn't a page region. --}}
<div {{ $attributes->class(['c-illustration', 'c-illustration--strip' => ! $removeBackground]) }} aria-hidden="true">

<div class="c-illustration__figure">
  <img class="c-illustration__human" src="{{ $character }}" alt="" loading="lazy" aria-hidden="true">

  @if ($character2)
    <img class="c-illustration__human c-illustration__human--second" src="{{ $character2 }}" alt="" loading="lazy" aria-hidden="true">
  @endif

  @foreach ($objects as $object)
    <img class="c-illustration__item c-illustration__item--{{ ['left', 'right-top', 'right-bottom'][$loop->index] }}" src="{{ $object }}" alt="" loading="lazy" aria-hidden="true">
  @endforeach
</div>

</div>
@endif
