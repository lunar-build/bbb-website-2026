<div class="c-archive__results-header">
  <h2 class="c-archive__results-heading u-heading-3" tabindex="-1" data-archive-focus>{{ $resultsHeading }}</h2>
  <p class="c-archive__count" aria-live="polite" aria-atomic="true">
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
