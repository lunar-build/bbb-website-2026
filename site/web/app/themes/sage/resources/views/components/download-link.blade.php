@props([
    'url',
    'label' => 'Download',
    'fileSize' => '',
    'id' => null,
    'context' => '',
])

<div {{ $attributes->class(['c-download-link']) }}>
    <a
        class="c-download-link__link u-cta-large"
        href="{{ $url }}"
        download
        @if ($fileSize && $id) aria-describedby="{{ $id }}" @endif
    >
        <x-icon name="download" />
        {{ $label }}
        @if ($context)
            <span class="u-sr-only">({{ $context }})</span>
        @endif
    </a>

    @if ($fileSize)
        <span @if ($id) id="{{ $id }}" @endif class="c-download-link__file-size u-body-regular">{{ $fileSize }}</span>
    @endif
</div>
