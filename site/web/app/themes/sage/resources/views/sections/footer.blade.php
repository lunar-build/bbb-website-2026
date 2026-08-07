@php
  $footer = menu_items_to_footer_columns('footer_navigation');
  $copyright = sprintf('© %s %s', date('Y'), get_bloginfo('name'));
@endphp

<lunar-site-footer
  copyright="{{ $copyright }}"
  columns="{{ json_encode($footer['columns']) }}"
  legal="{{ json_encode($footer['legal']) }}"
>
  <a slot="logo" href="{{ home_url('/') }}">{!! $siteName !!}</a>

  <div slot="afterColumns">
    @php(dynamic_sidebar('sidebar-footer'))
  </div>
</lunar-site-footer>
