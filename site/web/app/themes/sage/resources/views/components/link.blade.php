@props(['link', 'icon' => 'arrow-right', 'labelClass' => null])

@php($newTab = ($link['target'] ?? '') === '_blank')

<a {{ $attributes->merge(['href' => $link['url']]) }} @if ($newTab) target="_blank" rel="noopener" @endif>
    @if ($labelClass)
        {{-- Outer span is the flex item; the inner one stays inline so a focus highlight follows each line. --}}
        <span><span class="{{ $labelClass }}">{{ $link['title'] }}</span></span>
    @else
        {{ $link['title'] }}
    @endif
    @if ($icon)
        <x-icon :name="$icon" />
    @endif
    @if ($newTab)
        <span class="u-sr-only">({{ __('opens in a new tab', 'sage') }})</span>
    @endif
</a>
