@php
  $items = menu_items_to_array('primary_navigation');
@endphp

<lunar-site-header sticky>
  <lunar-nav
    label="{{ wp_get_nav_menu_name('primary_navigation') ?: 'Primary' }}"
    items="{{ json_encode($items) }}"
  >
    <a slot="brand" href="{{ home_url('/') }}">{!! $siteName !!}</a>
  </lunar-nav>
</lunar-site-header>
