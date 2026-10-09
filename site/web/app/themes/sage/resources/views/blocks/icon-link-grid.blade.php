<section
  {{ $attributes->class(['c-icon-link-grid-block'])->except('style') }}
  style="{{ $attributes->get('style') }}@if ($backgroundColor) background: var(--wp--preset--color--{{ $backgroundColor }}); @endif"
>

<div class="o-container">
  <x-icon-link-grid :items="$items" />
</div>

</section>
