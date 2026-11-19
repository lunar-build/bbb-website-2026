{{-- No heading in this block, so a plain <div> rather than a <section> (a section is meant to have a heading). --}}
<div {{ $attributes->class(['c-rolling-stats-carousel-block']) }}>

  @if ($slides)
    <div class="o-container">
      <x-rolling-stats-carousel :slides="$slides" :image-left="$imageLeft" :image-right="$imageRight" />
    </div>
  @endif

</div>
