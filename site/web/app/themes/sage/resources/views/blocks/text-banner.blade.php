<section {{ $attributes->class(['c-text-banner', 'o-full-bleed-bg']) }}>

<div class="o-container">
  <p class="c-text-banner__text">
    {{ $text }}@if ($emphasis)<span class="c-text-banner__emphasis">{{ $emphasis }}</span>@endif
  </p>
</div>

</section>
