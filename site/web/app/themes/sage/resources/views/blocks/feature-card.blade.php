@unless ($block->preview)
  <div {{ $attributes->class(['c-feature-card']) }}>
@endunless

<wa-card class="c-feature-card__card" appearance="outlined" with-footer>
  <div class="c-feature-card__body">
    <strong class="c-feature-card__heading">{{ $heading }}</strong>
    <p class="c-feature-card__text">{{ $body }}</p>
  </div>

  <wa-button
    slot="footer"
    variant="brand"
    appearance="filled"
    href="{{ $link['url'] }}"
    aria-label="{{ sprintf(__('Read more about %s', 'sage'), $heading) }}"
    @if (($link['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif
  >
    {{ $link['title'] }}
  </wa-button>
</wa-card>

@unless ($block->preview)
  </div>
@endunless
