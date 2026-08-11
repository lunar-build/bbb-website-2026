@unless ($block->preview)
  <section {{ $attributes->class(['c-feature-card']) }}>
@endunless

<wa-card class="c-feature-card__card" appearance="outlined" with-footer>
  <div class="c-feature-card__body">
    <InnerBlocks template="{{ $block->template }}" />
  </div>

  <wa-button
    slot="footer"
    variant="brand"
    appearance="filled"
    href="{{ $link['url'] }}"
    @if (($link['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif
  >
    {{ $link['title'] }}
  </wa-button>
</wa-card>

@unless ($block->preview)
  </section>
@endunless
