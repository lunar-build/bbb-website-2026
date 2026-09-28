{{-- Example: <x-sticky-page-menu :items="[['label' => 'Bristol', 'url' => '#bristol', 'active' => true], ...]" /> --}}
{{-- Figma "Sticky article nav menu" — a page-index list of in-page section links, with the current section highlighted. This component only renders the list from given `items`; scroll-spy (updating `active` as the reader scrolls) and the sticky positioning itself are template-level concerns, wired up wherever this is used. --}}

@props([
    'items' => [], // [['label' => string, 'url' => string, 'active' => bool], ...]
])

@if (count($items))
    <nav {{ $attributes->class(['c-sticky-page-menu']) }} aria-label="{{ __('Page index', 'sage') }}">
        <p class="c-sticky-page-menu__heading u-table-column-heading">{{ __('Page index', 'sage') }}</p>

        <ul class="c-sticky-page-menu__list">
            @foreach ($items as $item)
                <li class="c-sticky-page-menu__item @if ($item['active'] ?? false) c-sticky-page-menu__item--active @endif">
                    <a class="c-sticky-page-menu__link u-nav-sublink" href="{{ $item['url'] }}" @if ($item['active'] ?? false) aria-current="location" @endif>
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
@endif
