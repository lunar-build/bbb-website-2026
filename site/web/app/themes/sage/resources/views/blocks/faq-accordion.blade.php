@unless ($block->preview)
  <section {{ $attributes->class(['c-faq-accordion']) }}>
@endunless

  @if ($heading)
    <h2 class="c-faq-accordion__heading">{{ $heading }}</h2>
  @endif

  <wa-accordion class="c-faq-accordion__list">
    @foreach ($items as $item)
      <wa-accordion-item label="{{ $item['question'] }}">
        {!! $item['answer'] !!}
      </wa-accordion-item>
    @endforeach
  </wa-accordion>

@unless ($block->preview)
  </section>
@endunless
