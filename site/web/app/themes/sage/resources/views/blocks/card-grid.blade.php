<section {{ $attributes->class(['c-card-grid']) }}>

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
    @foreach ($cards as $card)
      @if ($cardStyle === 'image_card')
        <x-image-card :image="$card['image']" :link="$card['link']" class="c-card-grid__card" />
      @else
        <x-card
          :card-style="$cardStyle"
          :image="$card['image']"
          :date="$card['date']"
          :heading="$card['heading']"
          :body="$card['body']"
          :link="$card['link']"
          :cta-style="$ctaStyle"
          class="c-card-grid__card"
        />
      @endif
    @endforeach
  </div>
</div>

@if ($sideImage)
  <img class="c-card-grid__side-image" src="{{ $sideImage }}" alt="" aria-hidden="true">
@endif

</section>
