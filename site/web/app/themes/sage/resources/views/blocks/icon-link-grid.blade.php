<section
  {{ $attributes->class(['c-icon-link-grid-block']) }}
  @if ($backgroundColor) style="background: var(--wp--preset--color--{{ $backgroundColor }})" @endif
>

<div class="o-container">
  <x-icon-link-grid :items="$items" />
</div>

</section>
