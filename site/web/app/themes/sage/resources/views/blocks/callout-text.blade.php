@unless ($block->preview)
  <section {{ $attributes->class(['c-callout-text-block']) }}>
@endunless

<div class="o-container">
  <x-callout-text :text="$text" />
</div>

@unless ($block->preview)
  </section>
@endunless
