@if ($src)
<section {{ $attributes->class(['c-map-embed']) }}>

<div class="o-container">
  <iframe
    class="c-map-embed__frame"
    src="{{ $src }}"
    title="{{ $mapTitle ?: __('Map', 'sage') }}"
    style="height: {{ $height }}rem"
    loading="lazy"
    referrerpolicy="no-referrer-when-downgrade"
    allowfullscreen
  ></iframe>
</div>

</section>
@endif
