@unless ($block->preview)
  <section {{ $attributes->class(['c-quote-block']) }}>
@endunless

<div class="o-container">
  <x-quote :attribution-name="$attributionName" :attribution-role="$attributionRole">
    <InnerBlocks template="{{ $block->template }}" />
  </x-quote>
</div>

@unless ($block->preview)
  </section>
@endunless
