@props(['level' => 'h2', 'style' => 'match'])

@php
  $tag = in_array($level, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']) ? $level : 'h2';
  $styleClass = $style && $style !== 'match' ? 'u-heading-'.substr($style, 1) : null;
@endphp

<{{ $tag }} {{ $attributes->class([$styleClass]) }}>{{ $slot }}</{{ $tag }}>
