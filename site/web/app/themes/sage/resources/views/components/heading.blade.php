@props(['level' => 'h2', 'style' => 'match'])

@php
  $isText = $level === 'none';
  $tag = $isText ? 'div' : (in_array($level, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']) ? $level : 'h2');
  // A "Not a heading" div has no level to size it, so it falls back to Heading 3.
  $styleClass = $style && $style !== 'match' ? 'u-heading-'.substr($style, 1) : ($isText ? 'u-heading-3' : null);
@endphp

<{{ $tag }} {{ $attributes->class([$styleClass]) }}>{{ $slot }}</{{ $tag }}>
