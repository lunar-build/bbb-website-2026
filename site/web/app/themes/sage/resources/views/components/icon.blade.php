@php
  $path = get_theme_file_path('resources/svg/'.basename($name).'.svg');
@endphp

@if (file_exists($path))
  @php
    $svg = file_get_contents($path);

    // Namespace internal ids (clipPath, gradients, etc) so reusing the same
    // icon more than once on a page doesn't collide — duplicate ids make
    // url(#id) references resolve unreliably in some browsers (seen as the
    // footer logo's clip-path randomly failing to paint after a scroll
    // repaint, since it shares ids with the header's copy of the same svg).
    $uid = 'icon-'.uniqid();
    $svg = preg_replace_callback('/\bid="([^"]+)"/', fn ($m) => 'id="'.$uid.'-'.$m[1].'"', $svg);
    $svg = preg_replace_callback('/url\(#([^)]+)\)/', fn ($m) => 'url(#'.$uid.'-'.$m[1].')', $svg);
    $svg = preg_replace_callback('/(xlink:href|href)="#([^"]+)"/', fn ($m) => $m[1].'="#'.$uid.'-'.$m[2].'"', $svg);
  @endphp
  {{-- Decorative by default; pass aria-hidden="false" to override. --}}
  <span {{ $attributes->merge(['aria-hidden' => 'true'])->class(['c-icon', 'c-icon--'.$name]) }}>{!! $svg !!}</span>
@endif
