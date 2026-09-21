  <section {{ $attributes->class(['c-text-hero']) }}>

<div class="o-container">
  <x-heading :level="$heading['level']" :style="$heading['style']" class="c-text-hero__heading">
    {{ $heading['text'] }}
  </x-heading>
  <x-copy :style="$intro['style']" class="c-text-hero__intro">
    {!! $intro['text'] !!}
  </x-copy>
</div>

  </section>
