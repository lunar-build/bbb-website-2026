@unless ($block->preview)
  <section {{ $attributes->class(['c-image-card']) }}>
@endunless

<div class="o-container">
  <x-image-card :image="$image" :link="$link" />
</div>

@unless ($block->preview)
  </section>
@endunless
