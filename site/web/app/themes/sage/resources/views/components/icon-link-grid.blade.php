@props(['items' => []])

<div {{ $attributes->class(['c-icon-link-grid']) }}>
  @isset($slot)
    @if (trim($slot))
      <div class="c-icon-link-grid__heading">
        {{ $slot }}
      </div>
    @endif
  @endisset

  <ul class="c-icon-link-grid__list">
    @foreach ($items as $item)
      <li class="c-icon-link-grid__item">
        <a
          class="c-icon-link-grid__link"
          href="{{ $item['link']['url'] }}"
          @if (! empty($item['link']['target'])) target="{{ $item['link']['target'] }}" @endif
        >
          <x-icon name="{{ $item['icon'] }}" class="c-icon-link-grid__icon" />
          <span class="c-icon-link-grid__label">{{ $item['link']['title'] }}</span>
        </a>
      </li>
    @endforeach
  </ul>
</div>
