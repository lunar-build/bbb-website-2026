{{-- Group columns for a desktop dropdown, shared by each item's dropdown and the
     overflow menu. $groups: the item's children; $idPrefix keeps aria-labelledby ids unique. --}}
<div class="c-primary-nav-desktop__columns">
    @foreach ($groups as $g => $group)
        @php($groupHeadingId = $idPrefix . '-' . $g)
        <div class="c-primary-nav-desktop__column">
            <p class="c-primary-nav-desktop__heading" id="{{ $groupHeadingId }}">
                {{ $group['label'] }}</p>

            @if (!empty($group['children']))
                <ul class="c-primary-nav-desktop__sublist" role="list"
                    aria-labelledby="{{ $groupHeadingId }}">
                    @foreach ($group['children'] as $link)
                        <li>
                            <a href="{{ $link['href'] }}"
                                class="c-primary-nav-desktop__sublink">
                                <span>{{ $link['label'] }}</span>
                                <x-icon name="chevron"
                                    class="c-primary-nav-desktop__chevron c-primary-nav-desktop__chevron--sub" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endforeach
</div>
