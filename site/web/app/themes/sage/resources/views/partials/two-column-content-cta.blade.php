@if (! empty($link['url']))
  <wa-button
    class="c-two-column-content__cta"
    variant="brand"
    appearance="accent"
    pill
    with-end
    href="{{ $link['url'] }}"
    @if (($link['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif
  >
    {{ $link['title'] ?: __('Find out more', 'sage') }}
    <x-icon name="arrow-right" slot="end" />
  </wa-button>
@endif
