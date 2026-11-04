@props(['heading' => false])

{{-- A <section> is a meaningful region only with a heading, so a block that shows none is a plain <div>.
     $heading: a heading array from a Heading field (text/level/style), or true/false. --}}
@php($tag = (is_array($heading) ? App\Support\HeadingLevel::shows($heading) : (bool) $heading) ? 'section' : 'div')

<{{ $tag }} {{ $attributes }}>{{ $slot }}</{{ $tag }}>
