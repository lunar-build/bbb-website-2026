@props(['style' => 'body'])

@php
  $styleClass = $style === 'standfirst' ? 'u-standfirst' : null;
@endphp

{{-- The field behind this component already wraps its own paragraphs (ACF's
     'wpautop' textarea setting), so wrap in a <div>, not another <p> —
     otherwise every paragraph nests inside a spurious outer <p>. --}}
<div {{ $attributes->class([$styleClass]) }}>{{ $slot }}</div>
