<!doctype html>
<html @php(language_attributes())>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/favicon.ico" sizes="32x32 16x16">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ Illuminate\Support\Facades\Vite::asset('resources/images/favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ Illuminate\Support\Facades\Vite::asset('resources/images/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ Illuminate\Support\Facades\Vite::asset('resources/images/favicon/favicon-16x16.png') }}">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#2BACE2">
    @php(do_action('get_header'))
    @php(wp_head())

    @vite(['resources/js/app.js'])
  </head>

  <body @php(body_class())>
    @php(wp_body_open())

    <div id="app">
      <a class="u-sr-only" href="#main">
        {{ __('Skip to content', 'sage') }}
      </a>

      @include('sections.header')

      <main id="main" class="main">
        @yield('content')
      </main>

      @hasSection('sidebar')
        <aside class="sidebar">
          @yield('sidebar')
        </aside>
      @endif

      @include('sections.footer')
    </div>

    @php(do_action('get_footer'))
    @php(wp_footer())
  </body>
</html>
