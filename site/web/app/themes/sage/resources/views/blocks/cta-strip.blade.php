@unless ($block->preview)
  <div {{ $attributes->class(['c-cta-strip']) }}>
@endunless

<wa-card class="c-cta-strip__card" appearance="outlined" with-footer>
  <div class="c-cta-strip__body">
    <strong class="c-cta-strip__title">{{ $title }}</strong>
    <p class="c-cta-strip__text">{{ $body }}</p>
  </div>

  <wa-button
    slot="footer"
    variant="brand"
    appearance="outlined"
    href="{{ $link['url'] }}"
    aria-label="{{ sprintf(__('Read more about %s', 'sage'), $title) }}"
    @if (($link['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif
  >
    {{ $link['title'] }}
  </wa-button>
</wa-card>

@unless ($block->preview)
  </div>
@endunless
