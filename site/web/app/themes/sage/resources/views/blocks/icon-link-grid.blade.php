@unless ($block->preview)
  <section {{ $attributes->class(['c-icon-link-grid-block']) }}>
@endunless

<div class="o-container">
  <x-icon-link-grid :items="$items">
    <InnerBlocks template="{{ $block->template }}" />
  </x-icon-link-grid>
</div>

@unless ($block->preview)
  </section>
@endunless
