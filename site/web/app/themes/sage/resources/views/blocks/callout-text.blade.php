<section {{ $attributes->class(['c-callout-text-block']) }}>

@if ($text)
<div class="o-container">
  <x-callout-text :text="$text" />
</div>
@endif

</section>
