<div class="c-archive__results-header">
  <h2 class="c-archive__results-heading u-heading-3" tabindex="-1" data-archive-results-heading>{{ $resultsHeading }}</h2>
  {{-- With no events the callout says it, so the count is for screen readers only. Announced by
       the page's [data-archive-status] region after a filter update (archive-filters.js). --}}
  <p @class(['c-archive__count', 'u-sr-only' => $noCurrentEvents ?? false]) data-archive-count>
    {{ sprintf(_n('%d result found', '%d results found', $foundPosts, 'sage'), $foundPosts) }}
  </p>
</div>

@if (count($cards))
  <div class="c-archive__cards">
    @foreach ($cards as $card)
      <x-card
        card-style="news"
        :image="$card['image']"
        :date="$card['date']"
        :categories="$card['categories']"
        :heading="$card['heading']"
        :body="$card['body']"
        :link="$card['link']"
        cta-style="button"
        class="c-archive__card"
      />
    @endforeach
  </div>
@elseif ($noCurrentEvents ?? false)
  <x-callout-text>{{ __('Sorry, there are no current events. Please check back soon.', 'sage') }}</x-callout-text>
@else
  <p class="c-archive__empty">{{ __('Sorry, nothing matches those filters.', 'sage') }}</p>
@endif

@if (count($paginationLinks))
  <nav class="c-archive__pagination" aria-label="{{ __('Pagination', 'sage') }}">
    <ul>
      @foreach ($paginationLinks as $link)
        <li>{!! $link !!}</li>
      @endforeach
    </ul>
  </nav>
@endif
