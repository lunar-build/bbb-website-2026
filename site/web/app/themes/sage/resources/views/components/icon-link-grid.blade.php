@props(['items' => []])

<div {{ $attributes->class(['c-icon-link-grid']) }}>
  <div class="c-icon-link-grid__list">
    @foreach ($items as $item)
      <a
        class="c-icon-link-grid__item"
        href="{{ $item['link']['url'] }}"
        @if (($item['link']['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif
      >
        <x-icon name="{{ $item['icon'] }}" class="c-icon-link-grid__icon" />

        <span class="c-icon-link-grid__item-heading">
          {{-- Outer span is the flex item; the inner one stays inline so its highlight follows each line. --}}
          <span><span class="c-icon-link-grid__item-title">{{ $item['link']['title'] }}</span></span>
          <x-icon name="arrow-right" class="c-icon-link-grid__arrow" />
        </span>

        @if (! empty($item['description']))
          <p class="c-icon-link-grid__description">{{ $item['description'] }}</p>
        @endif
      </a>
    @endforeach
  </div>
</div>
