{{-- No @unless ($block->preview) guard here (unlike other blocks) — this
     block has no InnerBlocks, and its whole point (background colour per
     layout) is invisible without the <section>, which broke it on the
     pattern-library page where $block->preview is always true. --}}
<section {{ $attributes->class(['c-cta-banner', 'c-cta-banner--'.$layout]) }}>

@if ($layout === 'centred')
  @if ($imageLeft)
    <img class="c-cta-banner__image c-cta-banner__image--left" src="{{ $imageLeft }}" alt="">
  @endif

  @if ($imageRight)
    <img class="c-cta-banner__image c-cta-banner__image--right" src="{{ $imageRight }}" alt="">
  @endif
@endif

<div class="o-container">
  <div class="c-cta-banner__grid">
    <div class="c-cta-banner__content">
      @if ($heading)
        <h2 class="c-cta-banner__heading u-heading-1">{{ $heading }}</h2>
      @endif

      <p class="c-cta-banner__body u-standfirst">{{ $body }}</p>

      <wa-button
        class="c-cta-banner__cta"
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
    </div>

    @if ($layout === 'left' && $image)
      <img class="c-cta-banner__image--column" src="{{ $image }}" alt="">
    @endif
  </div>
</div>

</section>
