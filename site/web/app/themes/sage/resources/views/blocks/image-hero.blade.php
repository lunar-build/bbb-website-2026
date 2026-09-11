{{-- No @unless ($block->preview) guard here (unlike other blocks) — this
     block has no InnerBlocks, and its whole point (the background image)
     is invisible without the <section>, which broke it on the
     pattern-library page where $block->preview is always true. --}}
<section {{ $attributes->class(['c-image-hero']) }}>

@if ($backgroundImage['url'] ?? null)
  <img class="c-image-hero__background" src="{{ $backgroundImage['url'] }}" alt="">
@endif

@if ($showText)
  <div class="c-image-hero__overlay"></div>

  <div class="o-container">
    <div class="c-image-hero__content">
      @if ($showBreadcrumbs)
        <x-breadcrumbs
          :items="App\Support\Breadcrumbs::forPost()"
          style="--c-breadcrumbs-color: var(--wp--preset--color--white)"
        />
      @endif

      <h1 class="c-image-hero__heading">{{ $heading }}</h1>
    </div>
  </div>
@endif

</section>
