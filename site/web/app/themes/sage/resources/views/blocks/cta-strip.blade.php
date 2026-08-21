@unless ($block->preview)
  <section {{ $attributes->class(['c-cta-strip']) }}>
@endunless

<wa-card class="c-cta-strip__card" appearance="outlined" with-footer>
  <div class="c-cta-strip__body">
    <InnerBlocks template="{{ $block->template }}" />
  </div>

  <wa-button
    slot="footer"
    variant="brand"
    appearance="accent"
    pill
    with-end
    href="{{ $link['url'] }}"
    @if (($link['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif
  >
    {{ $link['title'] }}
    <x-icon name="arrow-right" slot="end" />
  </wa-button>
</wa-card>

@unless ($block->preview)
  </section>
@endunless
