@props(['heading' => false])

{{-- A <section> is a meaningful region only with a heading, so a block that shows none is a plain <div>.
     $heading: a heading array from a Heading field (text/level/style), or true/false. --}}
@php($tag = App\Support\HeadingLevel::tag($heading))

<{{ $tag }} {{ $attributes }}>{{ $slot }}</{{ $tag }}>
