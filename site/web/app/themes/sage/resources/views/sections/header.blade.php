@php
  $items = menu_items_to_array('primary_navigation');
@endphp

{{-- .o-container must wrap the custom element, never be applied directly to it:
     display:grid on a shadow-DOM host only grids its own shadow-tree wrapper,
     not slotted light-DOM content, so it can't constrain lunar-nav that way. --}}
<div class="o-container">
  <lunar-site-header sticky>
    <lunar-nav
      label="{{ wp_get_nav_menu_name('primary_navigation') ?: 'Primary' }}"
      items="{{ json_encode($items) }}"
    >
      <a slot="brand" href="{{ home_url('/') }}">{!! $siteName !!}</a>
    </lunar-nav>
  </lunar-site-header>
</div>
