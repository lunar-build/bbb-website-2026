{{-- No heading in this block, so a plain <div> rather than a <section> (a section is meant to have a heading). --}}
  <div {{ $attributes->class(['c-quote-block']) }}>

@if (trim(strip_tags((string) $quote)) !== '')
<div class="o-container">
  <x-quote :size="$quoteSize" :attribution-name="$attributionName" :attribution-role="$attributionRole" :image="$image">
    {!! $quote !!}
  </x-quote>
</div>
@endif

  </div>
