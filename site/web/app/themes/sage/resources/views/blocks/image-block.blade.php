<section {{ $attributes->class(['c-image-block']) }}>

<div class="o-container">
  <img class="c-image-block__image" src="{{ $image['url'] }}" alt="{{ $image['alt'] ?? '' }}">

  @if ($contentType === 'caption')
    @if ($caption)
      <p class="c-image-block__caption u-body-large">{{ $caption }}</p>
    @endif
  @else
    <div class="c-image-block__info-box">
      @if ($infoBoxType === 'wysiwyg')
        <div class="c-image-block__info-box-content">{!! $infoBoxContent !!}</div>
      @else
        <div class="c-image-block__info-box-columns">
          <div class="c-image-block__info-box-column">
            @if ($infoBoxRowsHeading)
              <h3 class="c-image-block__info-box-heading u-heading-5">{{ $infoBoxRowsHeading }}</h3>
            @endif

            <ul class="c-image-block__info-box-rows" role="list">
              @foreach ($infoBoxRows as $row)
                <li class="c-image-block__info-box-row">
                  <x-icon name="{{ $row['icon'] }}" class="c-image-block__info-box-icon" />
                  <span class="u-body-large">{{ $row['text'] }}</span>
                </li>
              @endforeach
            </ul>
          </div>

          <div class="c-image-block__info-box-column">
            <x-bullet-list
              :heading="$infoBoxHeading"
              heading-class="u-heading-5"
              :items="$infoBoxBullets"
              class="c-image-block__info-box-bullet-list"
            />
          </div>
        </div>
      @endif
    </div>
  @endif
</div>

</section>
