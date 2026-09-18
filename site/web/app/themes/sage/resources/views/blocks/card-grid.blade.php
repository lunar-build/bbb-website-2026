@unless ($block->preview)
  <section {{ $attributes->class(['c-card-grid']) }}>
@endunless

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
        :card-style="$cardStyle"
        :image="$card['image']"
        :date="$card['date']"
        :heading="$card['heading']"
        :body="$card['body']"
        :link="$card['link']"
        :cta-style="$ctaStyle"
        class="c-card-grid__card"
      />
    @endforeach
  </div>
</div>

@unless ($block->preview)
  </section>
@endunless
