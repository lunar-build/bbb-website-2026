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
        <nav class="c-pattern-library__nav" aria-label="{{ __('Blocks', 'sage') }}">
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
        </div>
      </div>
    </div>
  @endif
@endsection
