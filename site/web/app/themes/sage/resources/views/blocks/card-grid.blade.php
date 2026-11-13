<x-block-root :heading="$heading" {{ $attributes->class(['c-card-grid', 'o-full-bleed-bg']) }}>

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

  <div class="c-card-grid__cards @if ($sideImage) c-card-grid__cards--with-side-image @endif">
    @if ($cardStyle === 'business')
      @foreach ($businessCards as $card)
        <x-business-card
          :name="$card['name']"
          :contact-info="$card['contact_info']"
          :services="$card['services']"
          :description="$card['description']"
          :link="$card['link']"
          :cta-colour="$card['cta_colour'] ?? 'yellow'"
          :heading-level="$cardLevel"
          class="c-card-grid__card"
        />
      @endforeach
    @else
      @foreach ($cards as $card)
        @if ($cardStyle === 'image_card')
          <x-image-card :image="$card['image']" :link="$card['link']" class="c-card-grid__card" />
        @else
          <x-card
            :card-style="$cardStyle"
            :image="$card['image']"
            :date="$card['date']"
            :heading="array_merge($card['heading'], ['level' => $cardLevel])"
            :body="$card['body']"
            :link="$card['link']"
            :cta-style="$ctaStyle"
            class="c-card-grid__card"
          />
        @endif
      @endforeach
    @endif
  </div>
</div>

@if ($sideImage)
  <img class="c-card-grid__side-image" src="{{ $sideImage }}" alt="" aria-hidden="true">
@endif

</x-block-root>
