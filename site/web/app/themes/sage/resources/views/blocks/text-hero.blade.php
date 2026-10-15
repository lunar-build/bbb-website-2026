  <section {{ $attributes->class(['c-text-hero']) }}>

<div class="o-container">
  @if ($breadcrumbs)
    <x-breadcrumbs class="c-text-hero__breadcrumbs" :items="$breadcrumbs" />
  @endif

  <x-heading :level="$heading['level']" :style="$heading['style']" class="c-text-hero__heading">
    {{ $heading['text'] }}
  </x-heading>

  @if ($intro['text'])
    <x-copy :style="$intro['style']" class="c-text-hero__intro">
      {!! $intro['text'] !!}
    </x-copy>
  @endif
</div>

  </section>
