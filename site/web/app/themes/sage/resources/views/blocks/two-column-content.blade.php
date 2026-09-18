@unless ($block->preview)
  <section {{ $attributes->class(['c-two-column-content']) }}>
@endunless

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

@unless ($block->preview)
  </section>
@endunless
