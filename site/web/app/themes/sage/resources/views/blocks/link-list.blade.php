<section {{ $attributes->class(['c-link-list']) }}>

<div class="o-container c-link-list__group c-link-list--{{ str_replace('_', '-', $layout) }}">
  @if ($icon)
    <div class="c-link-list__icon">
      <x-icon name="{{ $icon }}" />
    </div>
  @endif

  @foreach ($links as $row)
    @if (str_starts_with($layout, 'short_'))
      <a class="c-link-list__short-link u-short-form-link" href="{{ $row['link']['url'] }}" @if (($row['link']['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif>
        {{ $row['link']['title'] }}
        <x-icon name="arrow-right" />
      </a>
    @else
      <div class="c-link-list__long-item">
        <a class="c-link-list__long-header" href="{{ $row['link']['url'] }}" @if (($row['link']['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif>
          <span class="c-link-list__long-title u-heading-5">{{ $row['link']['title'] }}</span>
          <x-icon name="arrow-right" />
        </a>

        @if ($layout === 'long_with_text' && $row['description'])
          <p class="c-link-list__long-description u-body-large">{{ $row['description'] }}</p>
        @endif
      </div>
    @endif
  @endforeach
</div>

</section>
