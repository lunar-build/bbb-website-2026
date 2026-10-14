  <section {{ $attributes->class(['c-quote-block']) }}>

@if (trim(strip_tags((string) $quote)) !== '')
<div class="o-container">
  <x-quote :size="$quoteSize" :attribution-name="$attributionName" :attribution-role="$attributionRole">
    {!! $quote !!}
  </x-quote>
</div>
@endif

  </section>
