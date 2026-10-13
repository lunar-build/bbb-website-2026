<div class="c-archive__results-header">
  <h2 class="c-archive__results-heading u-heading-3" tabindex="-1" data-archive-results-heading>{{ $resultsHeading }}</h2>
  <p class="c-archive__count" aria-live="polite" aria-atomic="true">
    {{ sprintf(_n('%d result found', '%d results found', $foundPosts, 'sage'), $foundPosts) }}
  </p>
</div>

@if (count($cards))
  <div class="c-archive__cards">
    @foreach ($cards as $card)
      <x-business-card
        class="c-archive__card"
        :id="$card['id']"
        tabindex="-1"
        :name="$card['name']"
        :contact-info="$card['contactInfo']"
        :services="$card['services']"
        :description="$card['description']"
        :regions="$card['regions']"
        :link="$card['link']"
      />
    @endforeach
  </div>
@else
  <p class="c-archive__empty">{{ __('Sorry, nothing matches those filters.', 'sage') }}</p>
@endif

{{-- Read by map-listing.js after every filter swap, so the pins follow the results. --}}
<script type="application/json" data-map-markers>@json($markers)</script>
