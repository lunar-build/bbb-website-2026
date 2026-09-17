{{-- No @unless ($block->preview) guard here (unlike other blocks) — the
     background colour is the whole point of this block on the
     pattern-library page, where $block->preview is always true (see
     CtaBanner for the same exception, documented there). --}}
<section
  {{ $attributes->class(['c-icon-link-grid-block']) }}
  @if ($backgroundColor) style="background: var(--wp--preset--color--{{ $backgroundColor }})" @endif
>

<div class="o-container">
  <x-icon-link-grid :items="$items">
    <InnerBlocks template="{{ $block->template }}" />
  </x-icon-link-grid>
</div>

</section>
