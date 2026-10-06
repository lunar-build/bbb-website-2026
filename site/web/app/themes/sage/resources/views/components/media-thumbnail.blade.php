@props([
  'type' => 'image',
  'thumbnail' => null,
  'videoUrl' => null,
  'alt' => '',
])

<span {{ $attributes->class(['c-media-thumbnail']) }}>
  @if ($type === 'video' && ! $thumbnail)
    {{-- No poster uploaded — preload="metadata" fetches only the file's
         header/first keyframe (not the whole video), and browsers render
         that as the frame shown before playback starts, same as a real
         poster image but with no extra upload step. --}}
    <video class="c-media-thumbnail__image" src="{{ $videoUrl }}" preload="metadata" muted playsinline aria-hidden="true" tabindex="-1"></video>
  @else
    <img class="c-media-thumbnail__image" src="{{ $thumbnail['url'] ?? '' }}" alt="{{ $alt }}">
  @endif

  @if ($type === 'video')
    <x-icon name="video-play" class="c-media-thumbnail__play" />
  @endif
</span>
