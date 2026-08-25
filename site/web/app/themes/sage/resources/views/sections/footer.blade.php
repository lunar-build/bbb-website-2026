@php
    $logoWhite = get_field('logo_white', 'option');
    $socialLinks = social_links_from_options();
    // The footer menu is 3 top-level (unrendered) parent items grouping the
    // visible links as children — see menu_items_to_footer_columns(). This
    // reuses the same header/footer nav location the header links from.
    $columns = menu_items_to_footer_columns('footer_navigation');
    $tagline = get_bloginfo('description');
    $copyright = sprintf('© %s %s', date('Y'), get_bloginfo('name'));
@endphp

<footer class="c-footer">
    <div class="c-footer-top">
        <div class="o-container">
          <div class="c-footer-top__row">
            <a class="c-footer-top__logo" href="{{ home_url('/') }}" aria-label="{{ get_bloginfo('name') }}">
                @if ($logoWhite)
                    <img src="{{ $logoWhite['url'] }}" alt="" class="c-logo c-logo--footer">
                @else
                    <x-icon name="logo" class="c-logo c-logo--footer" />
                @endif
            </a>

            <div class="c-footer-social">
                <div class="c-footer-social__icons">
                    @foreach ($socialLinks as $link)
                        <a href="{{ $link['url'] }}" class="c-footer-social__icon" aria-label="{{ ucfirst($link['platform']) }}"
                            target="_blank" rel="noopener noreferrer">
                            <x-icon name="{{ $link['platform'] }}" />
                        </a>
                    @endforeach
                </div>

                {{-- Static brand hashtags, not CMS-driven. --}}
                <div class="c-footer-social__hashtags">
                    <span>#betterbybike</span>
                    <span>#ibikeitilikeit</span>
                </div>
            </div>

            <nav class="c-footer-links" aria-label="Footer">
                @foreach ($columns as $column)
                    <div class="c-footer-links__group">
                        @foreach ($column['links'] as $link)
                            <a href="{{ $link['href'] }}" class="c-footer-links__link">
                                {{ $link['label'] }}
                                <x-icon name="arrow-right" />
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </nav>
          </div>
        </div>
    </div>

    <div class="c-footer-bottom">
        <div class="o-container">
            <p class="c-footer-bottom__copyright">{{ $copyright }}</p>
            @if ($tagline)
                <p class="c-footer-bottom__tagline">{{ $tagline }}</p>
            @endif
        </div>
    </div>
</footer>
