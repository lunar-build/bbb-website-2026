{{--
  Template Name: Pattern Library
--}}

@extends('layouts.app')

@section('content')
  @if (! is_user_logged_in())
    @php(status_header(404))

    <x-alert type="warning">
      {{ __('Sorry, but the page you are trying to view does not exist.', 'sage') }}
    </x-alert>
  @else
    {{-- .o-container only sets the shared max-width/gutter grid — the
    nav + content split lives on .c-pattern-library, one level in, so it
    gets its own grid-template-columns without clashing with .o-container's
    named grid lines (see resources/styles/base/_container.scss). --}}
    <div class="o-container">
      <div class="c-pattern-library">
        <nav class="c-pattern-library__nav" aria-label="{{ __('Pattern library navigation', 'sage') }}">
          <p class="c-pattern-library__nav-title">{{ __('Blocks', 'sage') }}</p>
          <ul>
            @foreach ($blocks as $block)
              <li>
                <a href="#{{ $block['slug'] }}">
                  @include('partials.pattern-library-icon', ['icon' => $block['icon']])
                  <span>{{ $block['name'] }}</span>
                </a>
              </li>
            @endforeach
          </ul>

          @if (WP_DEBUG)
            <p class="c-pattern-library__nav-title">{{ __('Components (dev only)', 'sage') }}</p>
            <ul>
              @foreach ($components as $component)
                <li><a href="#component-{{ $component['name'] }}"><span>{{ $component['name'] }}</span></a></li>
              @endforeach
              <li><a href="#style-buttons"><span>{{ __('Buttons', 'sage') }}</span></a></li>
              <li><a href="#style-type-scale"><span>{{ __('Type scale', 'sage') }}</span></a></li>
              <li><a href="#style-callout"><span>{{ __('Callout', 'sage') }}</span></a></li>
            </ul>
          @endif
        </nav>

        <div class="c-pattern-library__content">
          <div class="c-pattern-library__intro">
            <h1>{{ __('Pattern Library', 'sage') }}</h1>
            <p>{{ __('Every block available when building a page, shown with example content.', 'sage') }}</p>
          </div>

          @foreach ($blocks as $block)
            <section id="{{ $block['slug'] }}" class="c-pattern-library__entry">
              <div class="c-pattern-library__meta">
                <h2>
                  @include('partials.pattern-library-icon', ['icon' => $block['icon']])
                  {{ $block['name'] }}
                </h2>
                @if ($block['description'])
                  <p>{{ $block['description'] }}</p>
                @endif
              </div>

              <div class="c-pattern-library__preview">
                {!! $block['html'] !!}
              </div>
            </section>
          @endforeach

          @if (WP_DEBUG)
            <div class="c-pattern-library__intro">
              <h1>{{ __('Components', 'sage') }}</h1>
              <p>{{ __('Dev-only — base design-system pieces as they\'re built. Not shown to clients (gated on WP_DEBUG).', 'sage') }}</p>
            </div>

            @foreach ($components as $component)
              <section id="component-{{ $component['name'] }}" class="c-pattern-library__entry">
                <div class="c-pattern-library__meta">
                  <h2>&lt;x-{{ $component['name'] }}&gt;</h2>
                </div>

                <div class="c-pattern-library__preview">
                  @if ($component['html'])
                    {!! $component['html'] !!}
                  @else
                    <p>{{ __('No example yet — add one at resources/views/components/examples/', 'sage') }}{{ $component['name'] }}.blade.php</p>
                  @endif
                </div>
              </section>
            @endforeach

            @include('partials.pattern-library-styles')
          @endif
        </div>
      </div>
    </div>
  @endif
@endsection
