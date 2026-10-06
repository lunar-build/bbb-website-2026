<section {{ $attributes->class(['c-case-study-grid']) }}>

<div class="o-container">
  <div class="c-case-study-grid__cards">
    @foreach (['left', 'right'] as $column)
      <div class="c-case-study-grid__column c-case-study-grid__column--{{ $column }}">
        @foreach ($cards as $i => $card)
          @continue(($i % 2 === 0 ? 'left' : 'right') !== $column)
          <div class="c-case-study-grid__card">
            <img class="c-case-study-grid__image" src="{{ $card['image']['url'] }}" alt="{{ $card['image']['alt'] ?? '' }}">
            <x-quote class="c-case-study-grid__quote">{{ $card['quote'] }}</x-quote>
          </div>
        @endforeach
      </div>
    @endforeach
  </div>
</div>

</section>
