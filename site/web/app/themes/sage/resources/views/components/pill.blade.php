{{-- Example: <x-pill label="Infrastructure" url="{{ get_category_link($id) }}" /> --}}

@props([
    'label',
    'url' => null,
])

@if ($url)
    <a {{ $attributes->class(['c-pill']) }} href="{{ $url }}">
        {{ $label }}
        <x-icon name="arrow-right" class="c-pill__icon" />
    </a>
@else
    <span {{ $attributes->class(['c-pill']) }}>
        {{ $label }}
    </span>
@endif
