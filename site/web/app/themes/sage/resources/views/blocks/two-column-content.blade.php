  <x-block-root :heading="App\Support\HeadingLevel::shows($left['heading'] ?? null) || App\Support\HeadingLevel::shows($right['heading'] ?? null)" {{ $attributes->class(['c-two-column-content']) }}>

<div class="o-container">
  <div class="c-two-column-content__inner">
    <div class="c-two-column-content__column">
      @include('partials.two-column-content-slot', ['slot' => $left])
    </div>

    <div class="c-two-column-content__column">
      @include('partials.two-column-content-slot', ['slot' => $right])
    </div>
  </div>
</div>

  </x-block-root>
