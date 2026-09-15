@props([
    'src',
    'poster' => null,
    'label' => '',
    'variant' => 'background',
])

@php($isBackground = $variant === 'background')

{{--
  variant="background": decorative, muted, conveys no information (a hero
  background video) — the <video> is aria-hidden, but a pause/play toggle
  is still required whenever it autoplays, per WCAG 2.2.2 (Pause, Stop,
  Hide) — "decorative" doesn't exempt auto-playing, looping content from
  that rule. Toggle behaviour lives in resources/js/video-background.js.

  variant="content": real media, the video itself is the point — native
  controls, never aria-hidden, always keyboard reachable.
--}}
<div {{ $attributes->class(['c-video']) }} @if ($isBackground) data-video-background @endif>
    <video
        class="c-video__media"
        @if ($src) src="{{ $src }}" @endif
        @if ($poster) poster="{{ $poster }}" @endif
        @if ($isBackground)
            autoplay
            muted
            loop
            playsinline
            aria-hidden="true"
        @else
            controls
            @if ($label) aria-label="{{ $label }}" @endif
        @endif
    ></video>

    @if ($isBackground)
        <button
            type="button"
            class="c-video__control"
            data-video-control
            aria-pressed="true"
            aria-label="{{ __('Pause background video', 'sage') }}{{ $label ? ': '.$label : '' }}"
        >
            <svg class="c-video__control-icon c-video__control-icon--pause" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                <rect x="3" y="2" width="3" height="12" rx="0.5" />
                <rect x="10" y="2" width="3" height="12" rx="0.5" />
            </svg>
            <svg class="c-video__control-icon c-video__control-icon--play" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                <path d="M4 2.5v11l10-5.5-10-5.5Z" />
            </svg>
        </button>
    @endif
</div>
