{{-- Figma "Filter result card" (node 58:2560) — one Map Listing result. --}}

@props([
    'title',
    'details' => [], // [['icon' => string, 'text' => string, 'url' => string|null], ...] — address, phone...
    'services' => [], // [['icon' => string, 'label' => string], ...]
    'description' => null,
    'url' => null, // external site — opens in a new tab
])

<article {{ $attributes->class(['c-filter-result-card']) }}>
    <h3 class="c-filter-result-card__title">{{ $title }}</h3>

    @if (count($details))
        <ul class="c-filter-result-card__details">
            @foreach ($details as $detail)
                <li class="c-filter-result-card__detail">
                    <x-icon :name="$detail['icon']" class="c-filter-result-card__icon" />
                    @if ($detail['url'] ?? null)
                        <a href="{{ $detail['url'] }}">{{ $detail['text'] }}</a>
                    @else
                        <span>{{ $detail['text'] }}</span>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    @if (count($services))
        <ul class="c-filter-result-card__services" aria-label="{{ __('Services', 'sage') }}">
            @foreach ($services as $service)
                <li class="c-filter-result-card__service">
                    <x-icon :name="$service['icon']" class="c-filter-result-card__icon" />
                    {{ $service['label'] }}
                </li>
            @endforeach
        </ul>
    @endif

    @if ($description)
        <p class="c-filter-result-card__description">{{ $description }}</p>
    @endif

    @if ($url)
        <wa-button
            class="c-filter-result-card__cta"
            href="{{ $url }}"
            target="_blank"
            rel="noopener"
            variant="neutral"
            appearance="outlined"
            size="small"
            pill
            with-end
        >
            {{ __('Learn more', 'sage') }}<span class="u-sr-only"> {{ sprintf(__('about %s', 'sage'), $title) }}</span> {{ __('(opens in a new tab)', 'sage') }}
            <x-icon name="arrow-right" slot="end" />
        </wa-button>
    @endif
</article>
