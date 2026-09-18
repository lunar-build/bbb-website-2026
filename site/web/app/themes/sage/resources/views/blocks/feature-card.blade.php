{{-- The card-style modifier lives on <x-card> below, not this <section> —
     the <section> is skipped when $block->preview is true (both the real
     Gutenberg editor preview and the pattern-library page render with
     preview=true), which would make every card-style rule invisible on
     /pattern-library if it depended on this wrapper (see CtaBanner's own
     note on the same $block->preview quirk). --}}
@unless ($block->preview)
  <section {{ $attributes->class(['c-feature-card']) }}>
@endunless

<div class="o-container">
  <x-card
    :card-style="$cardStyle"
    :image="$image"
    :date="$date"
    :heading="$heading"
    :body="$body"
    :link="$link"
    :cta-style="$ctaStyle"
  />
</div>

@unless ($block->preview)
  </section>
@endunless
