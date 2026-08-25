@php
  $path = get_theme_file_path('resources/svg/'.basename($name).'.svg');
@endphp

@if (file_exists($path))
  {{-- Decorative by default — every current usage sits next to visible text
       or inside an element that already carries its own accessible name
       (aria-label on a social link, sr-only text on a toggle, etc), so the
       icon itself must stay out of the accessibility tree rather than
       risk being announced twice or with no name at all. Pass
       aria-hidden="false" explicitly for the rare icon that IS the whole
       accessible content and has its own <title> inside the SVG. --}}
  <span {{ $attributes->merge(['aria-hidden' => 'true'])->class(['c-icon', 'c-icon--'.$name]) }}>{!! file_get_contents($path) !!}</span>
@endif
