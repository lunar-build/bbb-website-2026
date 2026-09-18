@props(['style' => 'body'])

@php
  $styleClass = $style === 'standfirst' ? 'u-standfirst' : null;
@endphp

<p {{ $attributes->class([$styleClass]) }}>{{ $slot }}</p>
