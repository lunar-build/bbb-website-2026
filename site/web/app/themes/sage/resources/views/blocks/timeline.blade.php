@unless ($block->preview)
  <section {{ $attributes->class(['c-timeline-block']) }}>
@endunless

<div class="o-container">
  @if ($heading)
    <h2>{{ $heading }}</h2>
  @endif

  @if ($intro)
    <p class="u-standfirst">{{ $intro }}</p>
  @endif

  <x-timeline :steps="$steps" />
</div>

@unless ($block->preview)
  </section>
@endunless
