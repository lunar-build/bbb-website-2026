<section {{ $attributes->class(['c-link-card']) }}>

<div class="o-container">
  @foreach ($cards as $card)
    <div class="c-link-card__card @if (! $card['logo']) c-link-card__card--no-logo @endif">
      @if ($card['logo'])
        <div class="c-link-card__logo">
          <img src="{{ $card['logo']['url'] }}" alt="{{ $card['logo']['alt'] ?? '' }}">
        </div>
      @endif

      <div class="c-link-card__content">
        @if ($card['title'])
          <p class="c-link-card__title u-heading-4">{{ $card['title'] }}</p>
        @endif

        @if ($card['description'])
          <p class="c-link-card__description u-body-regular">{{ $card['description'] }}</p>
        @endif
      </div>

      <div class="c-link-card__cta">
        @if ($card['cta_type'] === 'download')
          <a class="c-link-card__cta-link u-cta-large" href="{{ $card['file']['url'] }}" download>
            <x-icon name="download" />
            {{ $card['cta_label'] }}
          </a>

          @if ($card['file_size'])
            <span class="c-link-card__file-size u-body-regular">{{ $card['file_size'] }}</span>
          @endif
        @else
          <a class="c-link-card__cta-link u-cta-large" href="{{ $card['link']['url'] }}" @if (($card['link']['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif>
            {{ $card['link']['title'] }}
            <x-icon name="arrow-right" />
          </a>
        @endif
      </div>
    </div>
  @endforeach
</div>

</section>
