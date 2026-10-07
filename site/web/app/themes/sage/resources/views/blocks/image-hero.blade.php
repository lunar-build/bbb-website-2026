<section {{ $attributes->class(['c-image-hero', 'o-full-bleed-bg']) }}>

@if ($backgroundImage['url'] ?? null)
  <img class="c-image-hero__background" src="{{ $backgroundImage['url'] }}" alt="" style="object-position: center {{ $imagePosition }};">
@endif

@if ($showHeading || $showBreadcrumbs)
  <div class="c-image-hero__overlay"></div>

  <div class="o-container">
    <div class="c-image-hero__content">
      @if ($showBreadcrumbs)
        <x-breadcrumbs
          :items="App\Support\Breadcrumbs::forPost()"
          style="--c-breadcrumbs-color: var(--wp--preset--color--white)"
        />
      @endif

      @if ($showHeading)
        <h1 class="c-image-hero__heading">{{ $heading }}</h1>
      @endif
    </div>
  </div>
@endif

</section>
