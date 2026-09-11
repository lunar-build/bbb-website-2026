@php
  $path = get_theme_file_path('resources/svg/'.basename($name).'.svg');
@endphp

@if (file_exists($path))
  <span {{ $attributes->class(['c-icon', 'c-icon--'.$name]) }}>{!! file_get_contents($path) !!}</span>
@endif
