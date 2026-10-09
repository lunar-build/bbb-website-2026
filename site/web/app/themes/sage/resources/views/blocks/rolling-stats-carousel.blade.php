<section {{ $attributes->class(['c-rolling-stats-carousel-block']) }}>

  @if ($slides)
    <div class="o-container">
      <x-rolling-stats-carousel :slides="$slides" :image-left="$imageLeft" :image-right="$imageRight" />
    </div>
  @endif

</section>
