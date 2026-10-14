<section {{ $attributes->class(['c-case-study-grid']) }}>

<div class="o-container">
  <div class="c-case-study-grid__cards">
    @foreach ($cards as $card)
      <div class="c-case-study-grid__card">
        <img class="c-case-study-grid__image" src="{{ $card['image']['url'] }}" alt="{{ $card['image']['alt'] ?? '' }}">
        <x-quote
          class="c-case-study-grid__quote"
          :attribution-name="$card['name'] ?: null"
          :attribution-role="$card['info'] ?: null"
        >{{ $card['quote'] }}</x-quote>

        @if ($card['link']['url'])
          <div class="c-case-study-grid__cta">
            <wa-button
              variant="brand"
              appearance="accent"
              size="small"
              pill
              with-end
              href="{{ $card['link']['url'] }}"
              @if ($card['link']['target'] === '_blank') target="_blank" rel="noopener" @endif
            >
              {{ $card['link']['title'] ?: __('Read their story', 'sage') }}
              <x-icon name="arrow-right" slot="end" />
            </wa-button>
          </div>
        @endif
      </div>
    @endforeach
  </div>
</div>

</section>
