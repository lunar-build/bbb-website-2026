{{-- No heading in this block, so a plain <div> rather than a <section> (a section is meant to have a heading). --}}
<div {{ $attributes->class(['c-callout-text-block']) }}>

@if ($text)
<div class="o-container">
  <x-callout-text :text="$text" />
</div>
@endif

</div>
