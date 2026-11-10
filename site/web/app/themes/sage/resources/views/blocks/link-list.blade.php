<section {{ $attributes->class(['c-link-list']) }}>

<div class="o-container">
  <div class="c-link-list__group c-link-list--{{ str_replace('_', '-', $layout) }}">
    @if ($icon)
      <div class="c-link-list__icon">
        <x-icon name="{{ $icon }}" />
      </div>
    @endif

    @foreach ($links as $row)
      @if (str_starts_with($layout, 'short_'))
        <a class="c-link-list__short-link u-short-form-link-small" href="{{ $row['link']['url'] }}" @if (($row['link']['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif>
          {{-- Outer span is the flex item; the inner one stays inline so its highlight follows each line. --}}
          <span><span class="c-link-list__short-text">{{ $row['link']['title'] }}</span></span>
          <x-icon name="arrow-right" />
        </a>
      @else
        <div class="c-link-list__long-item">
          <a class="c-link-list__long-header" href="{{ $row['link']['url'] }}" @if (($row['link']['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif>
            <span class="c-link-list__long-title u-heading-5"><span class="c-link-list__long-title-text">{{ $row['link']['title'] }}</span></span>
            <x-icon name="arrow-right" />
          </a>

          @if ($layout === 'long_with_text' && $row['description'])
            <p class="c-link-list__long-description u-body-large">{{ $row['description'] }}</p>
          @endif
        </div>
      @endif
    @endforeach
  </div>
</div>

</section>
