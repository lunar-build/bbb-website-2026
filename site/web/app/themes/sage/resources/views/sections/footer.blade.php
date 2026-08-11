@php
  $columns = menu_items_to_footer_columns('footer_navigation');
  $legal = legal_links_from_options();
  $copyright = sprintf('© %s %s', date('Y'), get_bloginfo('name'));
@endphp

<lunar-site-footer
  copyright="{{ $copyright }}"
  columns="{{ json_encode($columns) }}"
  legal="{{ json_encode($legal) }}"
>
  <a slot="logo" href="{{ home_url('/') }}">{!! $siteName !!}</a>
</lunar-site-footer>
