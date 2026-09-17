@props(['items' => []])

<div {{ $attributes->class(['c-icon-link-grid']) }}>
  @if (trim($slot ?? ''))
    <div class="c-icon-link-grid__heading">
      {{ $slot }}
    </div>
  @endif

  <div class="c-icon-link-grid__list">
    @foreach ($items as $item)
      <a
        class="c-icon-link-grid__item"
        href="{{ $item['link']['url'] }}"
        @if (($item['link']['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif
      >
        <x-icon name="{{ $item['icon'] }}" class="c-icon-link-grid__icon" />

        <span class="c-icon-link-grid__item-heading">
          {{ $item['link']['title'] }}
          <x-icon name="arrow-right" class="c-icon-link-grid__arrow" />
        </span>

        @if (! empty($item['description']))
          <p class="c-icon-link-grid__description">{{ $item['description'] }}</p>
        @endif
      </a>
    @endforeach
  </div>
</div>
