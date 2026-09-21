  <section {{ $attributes->class(['c-quote-block']) }}>

<div class="o-container">
  <x-quote :size="$quoteSize" :attribution-name="$attributionName" :attribution-role="$attributionRole">
    {!! $quote !!}
  </x-quote>
</div>

  </section>
