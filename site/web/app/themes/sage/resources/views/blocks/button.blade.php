<section {{ $attributes->class(['c-button-block']) }}>

<div class="o-container">
  <div class="c-button-block__inner c-button-block__inner--{{ $align }}">
    <wa-button
      variant="{{ $style === 'brand' ? 'brand' : 'neutral' }}"
      appearance="{{ $style === 'outlined' ? 'outlined' : 'accent' }}"
      @if ($size === 'small') size="small" @endif
      pill
      with-end
      href="{{ $link['url'] }}"
      @if (($link['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif
    >
      {{ $link['title'] }}
      @if (($link['target'] ?? '') === '_blank')
        <span class="u-sr-only">({{ __('opens in a new tab', 'sage') }})</span>
      @endif
      <x-icon name="arrow-right" slot="end" />
    </wa-button>
  </div>
</div>

</section>
