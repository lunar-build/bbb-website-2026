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
