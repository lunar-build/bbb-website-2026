@if (count($cards))
<x-block-root :heading="$heading" {{ $attributes->class(['c-card-grid', 'c-child-page-cards', 'o-full-bleed-bg']) }}>

{{-- Card headings sit one level below the block's heading, or at its level when it shows none. --}}
@php($cardLevel = App\Support\HeadingLevel::inside($heading))

<div class="o-container">
  @if (! empty($heading['text']))
    <x-heading :level="$heading['level']" :style="$heading['style']" class="c-card-grid__heading">
      {{ $heading['text'] }}
    </x-heading>
  @endif

  @if (! empty($intro['text']))
    <x-copy :style="$intro['style']" class="c-card-grid__intro">
      {!! $intro['text'] !!}
    </x-copy>
  @endif

  <div class="c-card-grid__cards">
    @foreach ($cards as $card)
      <x-card
        card-style="link"
        :image="$card['image']"
        :heading="array_merge($card['heading'], ['level' => $cardLevel])"
        :body="$card['body']"
        :link="$card['link']"
        cta-style="icon"
        class="c-card-grid__card"
      />
    @endforeach
  </div>
</div>

</x-block-root>
@endif
