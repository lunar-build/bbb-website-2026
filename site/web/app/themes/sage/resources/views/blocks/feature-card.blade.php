@unless ($block->preview)
  <section {{ $attributes->class(['c-feature-card']) }}>
@endunless

<div class="o-container">
<wa-card class="c-feature-card__card" appearance="outlined" with-footer>
  <div class="c-feature-card__body">
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
</div>

@unless ($block->preview)
  </section>
@endunless
