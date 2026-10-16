{{--
  Single MEC event (Figma "Single events page", node 14:1043). Same shape as
  the Sticky Nav template: optional hero block, page index from the content's
  H2s, then breadcrumbs, title, the featured image with the Next session /
  Price card, and the editor's own blocks. MEC only supplies the data
  (App\View\Composers\SingleMecEvent); none of its templates are used.
--}}

@extends('layouts.app')

@section('content')
  @while (have_posts()) @php(the_post())
    {!! $hero !!}

    <div class="o-container">
      <div @if (count($pageMenuItems)) class="c-sticky-nav-template" @else class="c-event" @endif>
        @if (count($pageMenuItems))
          <div class="c-sticky-nav-template__nav">
            <x-sticky-page-menu :items="$pageMenuItems" data-sticky-nav-menu />
          </div>
        @endif

        <div class="c-sticky-nav-template__content">
          <x-breadcrumbs :items="$breadcrumbs" />

          <h1 class="c-event__title">{!! get_the_title() !!}</h1>

          <div class="c-event__summary">
            @if (has_post_thumbnail())
              {!! get_the_post_thumbnail(null, 'large', ['class' => 'c-event__image']) !!}
            @endif

            <div class="c-event__info">
              <div class="c-event__info-column">
                <h2 class="c-event__info-heading u-heading-5">{{ $nextSession['heading'] ?? __('Next session', 'sage') }}</h2>
                @if ($nextSession)
                  <p class="c-event__info-line">
                    <x-icon name="calendar" />
                    {{ $nextSession['date'] }}
                  </p>
                  @if ($nextSession['time'])
                    <p class="c-event__info-line">
                      <x-icon name="clock" />
                      {{ $nextSession['time'] }}
                    </p>
                  @endif
                @else
                  <p class="c-event__info-line">{{ __('No sessions are scheduled at the moment.', 'sage') }}</p>
                @endif

                @if ($venue)
                  <div class="c-event__info-line c-event__venue">
                    <x-icon name="map-pin" />
                    <p>
                      {{ $venue['name'] }}@if ($venue['address']), {{ $venue['address'] }}@endif
                      <br>
                      <a href="{{ $venue['map'] }}" target="_blank" rel="noopener">
                        {{ __('View on map', 'sage') }}<span class="u-sr-only"> {{ __('(opens in a new tab)', 'sage') }}</span>
                      </a>
                    </p>
                  </div>
                @endif

                @if ($nextSession['finished'] ?? false)
                  <x-callout-text class="c-event__finished">{{ __('This event has finished.', 'sage') }}</x-callout-text>
                @endif
              </div>

              @if ($priceLines)
                <div class="c-event__info-column">
                  <h2 class="c-event__info-heading u-heading-5">{{ __('Price', 'sage') }}</h2>
                  <x-bullet-list class="c-event__price" :items="$priceLines" />
                </div>
              @endif
            </div>
          </div>

          {!! $body !!}
        </div>
      </div>
    </div>
  @endwhile
@endsection
