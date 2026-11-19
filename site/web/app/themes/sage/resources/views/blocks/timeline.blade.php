{{-- A <section> is only a section with a heading; without one it is a <div>. --}}
@php($tag = App\Support\HeadingLevel::tag((bool) $heading))

@unless ($block->preview)
  <{{ $tag }} {{ $attributes->class(['c-timeline-block']) }}>
@endunless

<div class="o-container">
  @if ($heading)
    <x-heading :level="$headingLevel" style="h2">{{ $heading }}</x-heading>
  @endif

  @if ($intro)
    <p class="u-standfirst">{{ $intro }}</p>
  @endif

  <x-timeline :steps="$steps" :marker-size="$markerSize" :heading-level="App\Support\HeadingLevel::inside(['text' => $heading, 'level' => $headingLevel])" />
</div>

@unless ($block->preview)
  </{{ $tag }}>
@endunless
