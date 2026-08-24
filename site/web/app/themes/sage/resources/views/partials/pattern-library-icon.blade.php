{{--
  Renders a block's `$icon` (Log1x\AcfComposer\Block::getIcon()) — either a
  WP core dashicon slug (e.g. "id-alt") or raw SVG markup (an "asset:"-prefixed
  icon). Array icons (foreground/background colour pairs) aren't rendered here.
--}}
@if (is_string($icon) && str_starts_with(trim($icon), '<svg'))
  {!! $icon !!}
@elseif (is_string($icon) && $icon !== '')
  <span class="dashicons dashicons-{{ $icon }}"></span>
@endif
