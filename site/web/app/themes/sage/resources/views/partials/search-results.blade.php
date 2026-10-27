<h2 class="u-sr-only" tabindex="-1" data-archive-results-heading>{{ __('Results', 'sage') }}</h2>

@if (count($results))
  <ol class="c-search__results c-link-card" role="list">
    @foreach ($results as $result)
      <li class="c-link-card__card c-link-card__card--no-logo c-search__result">
        <div class="c-link-card__content">
          <x-pill class="c-search__tag" :label="$result['tag']" />
          <h3 class="c-link-card__title c-search__result-title u-heading-5">{{ $result['title'] }}</h3>

          @if ($result['summary'])
            <p class="c-link-card__description u-body-regular">{{ $result['summary'] }}</p>
          @endif
        </div>

        <div class="c-link-card__cta">
          <a class="c-link-card__cta-link u-cta-large" href="{{ $result['url'] }}">
            {{ __('Read', 'sage') }}<span class="u-sr-only">: {{ $result['title'] }}</span>
            <x-icon name="arrow-right" />
          </a>
        </div>
      </li>
    @endforeach
  </ol>
@else
  <p class="c-search__empty u-standfirst">{{ __('Sorry, nothing matched your search. Try different words, or fewer filters.', 'sage') }}</p>
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
