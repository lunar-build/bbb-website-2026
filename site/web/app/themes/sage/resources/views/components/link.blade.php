@props(['link', 'icon' => 'arrow-right'])

@php($newTab = ($link['target'] ?? '') === '_blank')

<a {{ $attributes->merge(['href' => $link['url']]) }} @if ($newTab) target="_blank" rel="noopener" @endif>
    {{ $link['title'] }}
    @if ($icon)
        <x-icon :name="$icon" />
    @endif
    @if ($newTab)
        <span class="u-sr-only">({{ __('opens in a new tab', 'sage') }})</span>
    @endif
</a>
