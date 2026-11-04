{{-- A <section> is only a section with a heading; without one it is a <div>. --}}
@php($tag = App\Support\HeadingLevel::tag((bool) $heading))

@unless ($block->preview)
  <{{ $tag }} {{ $attributes->class(['c-accordion']) }}>
@endunless

@if ($items)
  <div class="o-container">
    @if ($heading)
      <x-heading :level="$headingLevel" style="h2" class="c-accordion__heading">{{ $heading }}</x-heading>
    @endif

    <wa-accordion class="c-accordion__list" appearance="plain">
      @foreach ($items as $item)
        <wa-accordion-item
          label="{{ $item['title'] }}"
          @if ($loop->first) expanded @endif
        >
          <x-icon name="chevron" slot="icon" class="c-accordion__icon" />
          <div class="c-accordion__content u-rich-text">{!! $item['content'] !!}</div>
        </wa-accordion-item>
      @endforeach
    </wa-accordion>
  </div>
@endif

@unless ($block->preview)
  </{{ $tag }}>
@endunless
