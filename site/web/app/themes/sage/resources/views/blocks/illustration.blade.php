@if ($url)
<section {{ $attributes->class(['c-illustration', 'c-illustration--'.$position]) }}>

<div class="o-container">
  <img class="c-illustration__image" src="{{ $url }}" alt="" loading="lazy" aria-hidden="true">
</div>

</section>
@endif
