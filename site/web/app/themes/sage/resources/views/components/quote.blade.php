{{--
  Reusable pull-quote fragment. Used by resources/views/blocks/quote.blade.php
  (default slot = the Quote block's rendered InnerBlocks content) and free to
  hardcode directly into any other block's Blade view later (default slot =
  literal text/HTML in that case instead).

  $size ('large'|'standard') matches the Figma Short/Long variants' two
  typographic scales — 'large' (default) suits a punchy one-liner, 'standard'
  suits a longer, multi-paragraph quote. $layout ('default'|'centered')
  covers the other visual arrangement seen across other blocks this
  component will be reused in (carousel/card, node 7:2013) — 'default' is
  the current mark-beside-text layout, 'centered' stacks the mark above
  centered text (see _quote.scss's .c-quote--centered for what it changes).
  Body/attribution colour are set via --c-quote-color/--c-quote-attribution-color
  CSS custom properties (again see _quote.scss) rather than hardcoded, so a
  future dark-background usage (node 7:1972) can override them from that
  block's own wrapper class without editing this component.

  Attribution is optional and hides entirely when $attributionName is
  empty; $attributionRole only renders when both are present. Note some
  reuses (e.g. the carousel card) show a name as its own heading rather
  than this cite-based attribution — that's the calling block's own
  markup, not something this component needs to support directly.
--}}

@props([
    'size' => 'large',
    'layout' => 'default',
    'attributionName' => null,
    'attributionRole' => null,
])

<div {{ $attributes->class(['c-quote', 'c-quote--'.$size, 'c-quote--'.$layout]) }}>
    <x-icon name="quote-mark" class="c-quote__mark" />

    <blockquote class="c-quote__body">
        {{ $slot }}
    </blockquote>

    @if ($attributionName)
        <cite class="c-quote__attribution">
            <span class="c-quote__attribution-name">{{ $attributionName }}</span>
            @if ($attributionRole)
                <span class="c-quote__attribution-role">{{ $attributionRole }}</span>
            @endif
        </cite>
    @endif
</div>
