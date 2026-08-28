{{-- Example: <x-breadcrumbs :items="App\Support\Breadcrumbs::forPost()" /> --}}
{{-- Colour variation: .c-breadcrumbs sets --c-breadcrumbs-color: var(--wp--preset--color--blue-dark) as the default. Every wa-breadcrumb-item and the arrow icon read from that var, not a hardcoded hex. - Override per block by setting the var on the tag or any ancestor — no SCSS edit needed:
<x-breadcrumbs :items="..." style="--c-breadcrumbs-color: var(--wp--preset--color--white)" /> --}}


@props([
    'items' => [], // [['label' => string, 'url' => string|null], ...] — last item's url is ignored (current page)
])

@if (count($items))
    <wa-breadcrumb {{ $attributes->class(['c-breadcrumbs']) }} label="{{ __('Breadcrumb', 'sage') }}">
        <span slot="separator" aria-hidden="true"><x-icon name="arrow-right" /></span>
        @foreach ($items as $item)
            <wa-breadcrumb-item @if ($item['url'] ?? null) href="{{ $item['url'] }}" @endif>
                {{ $item['label'] }}
            </wa-breadcrumb-item>
        @endforeach
    </wa-breadcrumb>
@endif
