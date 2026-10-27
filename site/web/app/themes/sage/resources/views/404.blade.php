@extends('layouts.app')

@section('content')
  <section class="c-not-found">
    <div class="o-container">
      <h1 class="c-not-found__title u-heading-2">{{ __('Whoops… we couldn’t find that page.', 'sage') }}</h1>
      <p class="c-not-found__intro u-standfirst">{{ __('Try one of these, or search for something else.', 'sage') }}</p>

      <form role="search" aria-label="{{ __('Search this site', 'sage') }}" method="get" action="{{ home_url('/') }}" class="c-not-found__search">
        <input type="search" name="s" placeholder="{{ __('What are you looking for?', 'sage') }}"
          aria-label="{{ __('What are you looking for?', 'sage') }}">
        <button type="submit" class="c-not-found__search-submit">
          <x-icon name="search" />
          <span>{{ __('Search', 'sage') }}</span>
        </button>
      </form>

      <ul class="c-not-found__links" role="list">
        @foreach (not_found_links_from_options() as $link)
          <li>
            <wa-button variant="brand" appearance="accent" pill with-end href="{{ $link['href'] }}"
              @if ($link['target'] === '_blank') target="_blank" rel="noopener" @endif>
              {{ $link['label'] }}
              @if ($link['target'] === '_blank')
                <span class="u-sr-only">({{ __('opens in a new tab', 'sage') }})</span>
              @endif
              <x-icon name="arrow-right" slot="end" />
            </wa-button>
          </li>
        @endforeach
      </ul>
    </div>
  </section>
@endsection
