@unless ($block->preview)
  <section {{ $attributes->class(['c-callout-text-block']) }}>
@endunless

@if ($text)
<div class="o-container">
  <x-callout-text :text="$text" />
</div>
@endif

@unless ($block->preview)
  </section>
@endunless
