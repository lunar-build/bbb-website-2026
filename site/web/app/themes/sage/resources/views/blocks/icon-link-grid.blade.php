{{-- No heading in this block, so a plain <div> rather than a <section> (a section is meant to have a heading). --}}
<div {{ $attributes->class(['c-icon-link-grid-block', 'o-full-bleed-bg']) }}>

<div class="o-container">
  <x-icon-link-grid :items="$items" />
</div>

</div>
