{{-- The card-style modifier lives on the .c-feature-card__card div below,
     not this <section> — the <section> is skipped when $block->preview is
     true (both the real Gutenberg editor preview and the pattern-library
     page render with preview=true), which would make every card-style rule
     invisible on /pattern-library if it depended on this wrapper (see
     CtaBanner's own note on the same $block->preview quirk). --}}
@unless ($block->preview)
  <section {{ $attributes->class(['c-feature-card']) }}>
@endunless

<div class="o-container">
<div class="c-feature-card__card c-feature-card--{{ $cardStyle }}">
  <img class="c-feature-card__image" src="{{ $image['url'] }}" alt="{{ $image['alt'] ?? '' }}">

  {{-- Stretched-link overlay: makes the whole card clickable without nesting an
       anchor around the visible CTA below (invalid HTML). The CTA below is
       purely decorative (aria-hidden + tabindex="-1", pointer-events:none via
       _button.scss) — this is the only real, focusable link in the card. --}}
  @if (! empty($link['url']))
    <a
      class="c-feature-card__stretched-link"
      href="{{ $link['url'] }}"
      aria-label="{{ $link['title'] ?: get_the_title() }}"
      @if (($link['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif
    ></a>
  @endif

  <div class="c-feature-card__body">
    @if ($date)
      <p class="c-feature-card__date">{{ $date }}</p>
    @endif

    <div class="c-feature-card__content">
      <InnerBlocks template="{{ $block->template }}" />
    </div>

    @if (! empty($link['url']) && $ctaStyle !== 'none')
      <div class="c-feature-card__footer">
        @if ($ctaStyle === 'icon')
          <span class="c-feature-card__cta-icon" aria-hidden="true">
            <x-icon name="arrow-right" />
          </span>
        @else
          {{-- The actual Button component (see resources/styles/components/_button.scss),
               marked decorative since the card itself — not this button — is the real link. --}}
          <wa-button
            class="c-feature-card__cta"
            variant="neutral"
            appearance="accent"
            size="small"
            pill
            with-end
            tabindex="-1"
            aria-hidden="true"
          >
            {{ $link['title'] ?: __('Find out more', 'sage') }}
            <x-icon name="arrow-right" slot="end" />
          </wa-button>
        @endif
      </div>
    @endif
  </div>
</div>
</div>

@unless ($block->preview)
  </section>
@endunless
