<section {{ $attributes->class(['c-card-row']) }}>

    <div class="o-container c-card-row--{{ $type }}">
        @if ($headingHighlight || $heading)
            <h2 class="c-card-row__heading">
                @if ($headingHighlight)
                    <span class="c-card-row__heading-highlight">{{ $headingHighlight }}</span>
                @endif
                {{ $heading }}
            </h2>
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
                        <h3 class="c-card-row__news-title">{{ $card['title'] }}</h3>
                        <a class="c-card-row__news-cta" href="{{ $card['link']['url'] }}"
                            @if (($card['link']['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif>
                            {{ $card['link']['title'] }}
                            <x-icon name="arrow-right" />
                            @if (($card['link']['target'] ?? '') === '_blank')
                                <span class="u-sr-only">({{ __('opens in a new tab', 'sage') }})</span>
                            @endif
                        </a>
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
                            <h3 class="c-cycle-route-card__route-name">{{ $card['route_name'] }}</h3>
                        </div>

                        <dl class="c-cycle-route-card__info">
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

                        <a class="c-cycle-route-card__cta" href="{{ $card['link']['url'] }}"
                            @if (($card['link']['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif>
                            {{ $card['link']['title'] }}
                            <x-icon name="arrow-right" />
                            @if (($card['link']['target'] ?? '') === '_blank')
                                <span class="u-sr-only">({{ __('opens in a new tab', 'sage') }})</span>
                            @endif
                        </a>
                    </article>
                @endforeach
            @elseif ($type === 'route')
                @foreach ($routeCards as $card)
                    <div class="c-cycle-route-card__card c-cycle-route-card--{{ $card['difficulty'] }}">
                        <div class="c-cycle-route-card__image">
                            <img src="{{ $card['image']['url'] }}" alt="{{ $card['image']['alt'] ?? '' }}"
                                style="object-position: {{ $imagePosition }}">
                        </div>

                        <div class="c-cycle-route-card__banner">
                            <span class="c-cycle-route-card__route-name">{{ $card['route_name'] }}</span>
                        </div>

                        <div class="c-cycle-route-card__info">
                            <div class="c-cycle-route-card__info-row">
                                <span class="c-cycle-route-card__info-label">{{ __('Time needed', 'sage') }}</span>
                                <span class="c-cycle-route-card__info-value">{{ $card['time_needed'] }}</span>
                            </div>

                            <div class="c-cycle-route-card__info-row">
                                <span class="c-cycle-route-card__info-label">{{ __('Distance', 'sage') }}</span>
                                <span class="c-cycle-route-card__info-value">{{ $card['distance'] }}</span>
                            </div>

                            <div class="c-cycle-route-card__info-row">
                                <span class="c-cycle-route-card__info-label">{{ __('Difficulty', 'sage') }}</span>
                                <span class="c-cycle-route-card__badge">{{ ucfirst($card['difficulty']) }}</span>
                            </div>
                        </div>

                        <a class="c-cycle-route-card__cta" href="{{ $card['link']['url'] }}"
                            @if (($card['link']['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif>
                            {{ $card['link']['title'] }}
                            <x-icon name="arrow-right" />
                        </a>
                    </div>
                @endforeach
            @else
                @foreach ($linkCards as $card)
                    <article class="c-card-row__link-card">
                        <img class="c-card-row__link-image" src="{{ $card['image']['url'] }}"
                            alt="{{ $card['image']['alt'] ?? '' }}" style="object-position: {{ $imagePosition }}">

                        <h3 class="c-card-row__link-title">{{ $card['title'] }}</h3>
                        <p class="c-card-row__link-body">{{ $card['body'] }}</p>

                        <a class="c-card-row__link-cta" href="{{ $card['link']['url'] }}"
                            @if (($card['link']['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif>
                            {{ $card['link']['title'] }}
                            <x-icon name="arrow-right" />
                            @if (($card['link']['target'] ?? '') === '_blank')
                                <span class="u-sr-only">({{ __('opens in a new tab', 'sage') }})</span>
                            @endif
                        </a>
                    </article>
                @endforeach
            @endif
        </div>

        @if (!empty($areaLinks))
            <div class="c-card-row__area-links">
                @foreach ($areaLinks as $row)
                    <a class="c-card-row__area-link" href="{{ $row['link']['url'] }}"
                        @if (($row['link']['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif>
                        {{ $row['link']['title'] }}
                        <x-icon name="arrow-right" />
                        @if (($row['link']['target'] ?? '') === '_blank')
                            <span class="u-sr-only">({{ __('opens in a new tab', 'sage') }})</span>
                        @endif
                    </a>
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

</section>
