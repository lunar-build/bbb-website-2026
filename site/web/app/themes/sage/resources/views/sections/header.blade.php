@php
    $items = menu_items_to_array('primary_navigation');
    $logoCharcoal = get_field('logo_charcoal', 'option');
@endphp

<div class="o-container">
    <lunar-site-header sticky>
        <lunar-nav label="{{ wp_get_nav_menu_name('primary_navigation') ?: 'Primary' }}"
            items="{{ json_encode($items) }}">
            <a slot="brand" href="{{ home_url('/') }}">
                @if ($logoCharcoal)
                    <img src="{{ $logoCharcoal['url'] }}" alt="{{ $logoCharcoal['alt'] ?: get_bloginfo('name') }}"
                        class="c-logo">
                @else
                    <x-icon name="logo" class="c-logo" />
                @endif
            </a>
        </lunar-nav>
    </lunar-site-header>
</div>
