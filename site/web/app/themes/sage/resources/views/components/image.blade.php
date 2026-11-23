@props(['image', 'size' => 'large', 'sizes' => null, 'alt' => null, 'priority' => false])

{{-- Media-library image with srcset/sizes, intrinsic dimensions and lazy loading, instead of the
     full-size original. `$image` is an ACF image array, an attachment ID or a URL. `priority` is for
     the above-the-fold (LCP) image: eager, high fetch priority. Falls back to a plain <img> when the
     URL isn't a media-library file (e.g. a bundled placeholder). --}}
@php
  $imageData = is_array($image) ? $image : ['url' => is_numeric($image) ? (string) wp_get_attachment_url((int) $image) : (string) $image];
  $attachmentId = is_numeric($image) ? (int) $image : (int) ($imageData['ID'] ?? $imageData['id'] ?? 0);

  if (! $attachmentId && ! empty($imageData['url'])) {
      $attachmentId = (int) attachment_url_to_postid($imageData['url']);
  }

  $imageAttributes = array_merge($attributes->getAttributes(), ['alt' => $alt ?? ($imageData['alt'] ?? '')]);

  if ($sizes) {
      $imageAttributes['sizes'] = $sizes;
  }

  if ($priority) {
      $imageAttributes['loading'] = 'eager';
      $imageAttributes['fetchpriority'] = 'high';
  }

  $imageMarkup = $attachmentId ? wp_get_attachment_image($attachmentId, $size, false, $imageAttributes) : '';
@endphp

@if ($imageMarkup)
  {!! $imageMarkup !!}
@elseif (! empty($imageData['url']))
  <img {{ $attributes->merge(['alt' => $alt ?? ($imageData['alt'] ?? ''), 'src' => $imageData['url'], 'loading' => $priority ? 'eager' : 'lazy', 'decoding' => 'async']) }}>
@endif
