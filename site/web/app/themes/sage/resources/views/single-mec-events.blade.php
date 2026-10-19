{{-- Figma "Single events page" (node 14:1043), on the Sticky Nav template's layout. Data: App\View\Composers\SingleMecEvent. --}}

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

          @if ($labels)
            <ul class="c-label-list c-event__labels" role="list" aria-label="{{ __('Labels', 'sage') }}">
              @foreach ($labels as $label)
                <li class="c-label" style="--label-bg: {{ $label['bg'] }}; --label-fg: {{ $label['fg'] }}">{{ $label['name'] }}</li>
              @endforeach
            </ul>
          @endif

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
                  @if ($nextSession['note'])
                    <p class="c-event__note">{{ $nextSession['note'] }}</p>
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

                @if ($organizer)
                  <div class="c-event__info-line c-event__venue">
                    <x-icon name="info" />
                    <p>
                      {{ $organizer['name'] }}
                      @if ($organizer['tel'])<br><a href="tel:{{ preg_replace('/[^+\d]/', '', $organizer['tel']) }}">{{ $organizer['tel'] }}</a>@endif
                      @if ($organizer['email'])<br><a href="mailto:{{ $organizer['email'] }}">{{ $organizer['email'] }}</a>@endif
                      @if ($organizer['url'])<br><a href="{{ $organizer['url'] }}">{{ __('Website', 'sage') }}</a>@endif
                    </p>
                  </div>
                @endif

                @if ($status)
                  <x-callout-text class="c-event__finished">{{ $status }}</x-callout-text>
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

          @if ($upcoming)
            <section class="c-event__upcoming" aria-labelledby="c-event-upcoming">
              <h2 id="c-event-upcoming" class="u-heading-5">{{ __('More upcoming sessions', 'sage') }}</h2>
              <x-bullet-list :items="array_map(fn($line) => ['text' => $line], $upcoming)" />
            </section>
          @endif

          {!! $body !!}

          @if ($jsonLd)
            <script type="application/ld+json">{!! $jsonLd !!}</script>
          @endif
        </div>
      </div>
    </div>
  @endwhile
@endsection
