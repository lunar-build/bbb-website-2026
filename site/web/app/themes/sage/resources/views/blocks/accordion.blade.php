@unless ($block->preview)
  <section {{ $attributes->class(['c-accordion']) }}>
@endunless

  <div class="o-container">
    @if ($heading)
      <h2 class="c-accordion__heading">{{ $heading }}</h2>
    @endif

    <wa-accordion class="c-accordion__list" appearance="plain">
      @foreach ($items as $item)
        <wa-accordion-item
          label="{{ $item['title'] }}"
          @if ($loop->first) expanded @endif
        >
          <x-icon name="chevron" slot="icon" class="c-accordion__icon" />
          <div class="c-accordion__content">{!! $item['content'] !!}</div>
        </wa-accordion-item>
      @endforeach
    </wa-accordion>
  </div>

@unless ($block->preview)
  </section>
@endunless
