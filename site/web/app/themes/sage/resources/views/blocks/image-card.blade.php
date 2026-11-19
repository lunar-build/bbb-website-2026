{{-- No heading in this block, so a plain <div> rather than a <section> (a section is meant to have a heading). --}}
  <div {{ $attributes->class(['c-image-card']) }}>

<div class="o-container">
  <x-image-card :image="$image" :link="$link" />
</div>

  </div>
