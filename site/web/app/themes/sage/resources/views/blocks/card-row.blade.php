@php
  $hasHeading = (bool) ($headingHighlight || $heading);
  // Card headings sit one level below the block's heading, or at its level when it shows none.
  $cardLevel = App\Support\HeadingLevel::inside(['text' => $hasHeading ? 'x' : '', 'level' => $headingLevel]);
@endphp

<x-block-root :heading="$hasHeading" {{ $attributes->class(['c-card-row']) }}>

    <div class="o-container c-card-row--{{ $type }}">
        @if ($headingHighlight || $heading)
            <x-heading :level="$headingLevel" style="h2" class="c-card-row__heading">
                @if ($headingHighlight)
                    <span class="c-card-row__heading-highlight">{{ $headingHighlight }}</span>
                @endif
                {{ $heading }}
            </x-heading>
        @endif

        @if ($intro)
            <p class="c-card-row__intro u-body-large">{{ $intro }}</p>
        @endif

        <div class="c-card-row__cards">
            @if ($type === 'news')
                @foreach ($newsCards as $card)
                    <article class="c-card-row__news-card">
                        <img class="c-card-row__news-image" src="{{ $card['image']['url'] }}"
                            alt="{{ $card['image']['alt'] ?? '' }}" style="object-position: {{ $imagePosition }}">

                        @if (!empty($card['tag']))
                            <span class="c-card-row__news-tag">{{ $card['tag'] }}</span>
                        @endif
                        <x-heading :level="$cardLevel" style="h3" class="c-card-row__news-title">{{ $card['title'] }}</x-heading>
                        <x-link class="c-card-row__news-cta" :link="$card['link']" />
                    </article>
                @endforeach
            @elseif ($type === 'route')
                @foreach ($routeCards as $card)
                    <article class="c-cycle-route-card__card c-cycle-route-card--{{ $card['difficulty'] }}">
                        <div class="c-cycle-route-card__image">
                            <img src="{{ $card['image']['url'] }}" alt="{{ $card['image']['alt'] ?? '' }}"
                                style="object-position: {{ $imagePosition }}">
                        </div>

                        <div class="c-cycle-route-card__banner">
                            <x-heading :level="$cardLevel" style="h3" class="c-cycle-route-card__route-name">{{ $card['route_name'] }}</x-heading>
                        </div>

                        <dl class="c-cycle-route-card__info"
                            aria-label="{{ sprintf(__('Details for %s', 'sage'), $card['route_name']) }}">
                            <div class="c-cycle-route-card__info-row">
                                <dt class="c-cycle-route-card__info-label">{{ __('Time needed', 'sage') }}</dt>
                                <dd class="c-cycle-route-card__info-value">{{ $card['time_needed'] }}</dd>
                            </div>

                            <div class="c-cycle-route-card__info-row">
                                <dt class="c-cycle-route-card__info-label">{{ __('Distance', 'sage') }}</dt>
                                <dd class="c-cycle-route-card__info-value">{{ $card['distance'] }}</dd>
                            </div>

                            <div class="c-cycle-route-card__info-row">
                                <dt class="c-cycle-route-card__info-label">{{ __('Difficulty', 'sage') }}</dt>
                                <dd class="c-cycle-route-card__badge">{{ ucfirst($card['difficulty']) }}</dd>
                            </div>
                        </dl>

                        <x-link class="c-cycle-route-card__cta" :link="$card['link']" />
                    </article>
                @endforeach
            @else
                @foreach ($linkCards as $card)
                    <article class="c-card-row__link-card">
                        <img class="c-card-row__link-image" src="{{ $card['image']['url'] }}"
                            alt="{{ $card['image']['alt'] ?? '' }}" style="object-position: {{ $imagePosition }}">

                        <x-heading :level="$cardLevel" style="h3" class="c-card-row__link-title">{{ $card['title'] }}</x-heading>
                        <p class="c-card-row__link-body">{{ $card['body'] }}</p>

                        <x-link class="c-card-row__link-cta" :link="$card['link']" />
                    </article>
                @endforeach
            @endif
        </div>

        @if (!empty($areaLinks))
            <div class="c-card-row__area-links">
                @foreach ($areaLinks as $row)
                    <x-link class="c-card-row__area-link" label-class="c-card-row__area-link-text" :link="$row['link']" />
                @endforeach
            </div>
        @endif

        @if ($browseAllLink)
            <wa-button class="c-card-row__browse-all" variant="{{ $type === 'route' ? 'neutral' : 'brand' }}"
                appearance="accent" pill with-end href="{{ $browseAllLink['url'] }}"
                @if (($browseAllLink['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif>
                {{ $browseAllLink['title'] }}
                @if (($browseAllLink['target'] ?? '') === '_blank')
                    <span class="u-sr-only">({{ __('opens in a new tab', 'sage') }})</span>
                @endif
                <x-icon name="arrow-right" slot="end" />
            </wa-button>
        @endif
    </div>

</x-block-root>
