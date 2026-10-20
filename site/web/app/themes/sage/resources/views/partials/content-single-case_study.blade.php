{{-- Interim layout borrowed from the news article (content-single-post) until
     case studies get their own design — see Trello "Design: Case study". --}}
@php($hero = $hero())
@php($quote = $quote())
@php($relatedPosts = $relatedPosts())

<article @php(post_class('h-entry o-container--narrow'))>
  <section class="c-post-hero">
    <div class="o-container">
      <x-post-hero :date="$hero['date']" :date-iso="$hero['dateIso']" :title="$hero['title']" :permalink="$hero['permalink']" :categories="$hero['categories']" />
    </div>
  </section>

  @if (has_post_thumbnail())
    <div class="o-container">
      <div class="c-single-post__image">
        {!! get_the_post_thumbnail(null, 'large', ['class' => 'c-single-post__image-el']) !!}
      </div>
    </div>
  @endif

  @if ($quote)
    <div class="o-container c-single-post__quote">
      <x-quote size="standard" :attribution-name="$hero['title']">{{ $quote }}</x-quote>
    </div>
  @endif

  <div class="o-container e-content">
    @php(the_content())
  </div>

  @if (count($relatedPosts))
    <div class="o-container c-single-post__related">
      <h2 class="c-single-post__related-heading u-heading-3">{{ __('More case studies', 'sage') }}</h2>

      <div class="c-single-post__related-cards">
        @foreach ($relatedPosts as $card)
          <x-card
            card-style="news"
            :image="$card['image']"
            :heading="$card['heading']"
            :body="$card['body']"
            :link="$card['link']"
            cta-style="button"
            class="c-single-post__related-card"
          />
        @endforeach
      </div>

      <wa-button
        class="c-single-post__related-cta"
        variant="brand"
        appearance="accent"
        pill
        with-end
        href="{{ get_post_type_archive_link('case_study') }}"
      >
        {{ __('All case studies', 'sage') }}
        <x-icon name="arrow-right" slot="end" />
      </wa-button>
    </div>
  @endif
</article>
