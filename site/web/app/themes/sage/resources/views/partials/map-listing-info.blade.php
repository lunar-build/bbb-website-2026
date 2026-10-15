{{-- A map pin's popup (map-listing.js), built from the same data as its <x-business-card>. --}}
<div class="c-map-listing__info">
  <h3 class="c-map-listing__info-name">{{ $card['name'] }}</h3>

  @foreach ($card['contactInfo'] as $row)
    <p class="c-map-listing__info-row">
      @if ($row['icon'] === 'phone')
        {{ __('Call:', 'sage') }} <a href="{{ $row['href'] }}">{{ $row['text'] }}</a>
      @else
        {{ $row['text'] }}
      @endif
    </p>
  @endforeach

  @if ($card['description'])
    <p class="c-map-listing__info-description">{{ $card['description'] }}</p>
  @endif

  <p class="c-map-listing__info-links">
    @if ($card['link'])
      <a href="{{ $card['link']['url'] }}" target="_blank" rel="noopener">{{ __('Visit website', 'sage') }}<span class="u-sr-only"> {{ __('(opens in a new tab)', 'sage') }}</span></a>
    @endif
    <a href="#{{ $card['id'] }}">{{ __('View details', 'sage') }}</a>
  </p>
</div>
