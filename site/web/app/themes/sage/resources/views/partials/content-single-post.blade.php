@php($hero = $hero())
@php($relatedPosts = $relatedPosts())
@php($pagination = $pagination())

<article @php(post_class('h-entry'))>
  <section class="c-post-hero">
    <div class="o-container o-container--narrow">
      <x-post-hero :date="$hero['date']" :title="$hero['title']" :permalink="$hero['permalink']" :categories="$hero['categories']" />
    </div>
  </section>

  @if (has_post_thumbnail())
    <div class="o-container o-container--narrow">
      <div class="c-single-post__image">
        {!! get_the_post_thumbnail(null, 'large', ['class' => 'c-single-post__image-el']) !!}
      </div>
    </div>
  @endif

  <div class="o-container o-container--narrow">
    <div class="e-content">
      @php(the_content())
    </div>

    @if ($pagination)
      <nav class="page-nav" aria-label="{{ __('Page', 'sage') }}">
        {!! $pagination !!}
      </nav>
    @endif
  </div>

  @if (count($relatedPosts))
    <div class="o-container o-container--narrow c-single-post__related">
      <h2 class="c-single-post__related-heading u-heading-3">{{ __('Also read:', 'sage') }}</h2>

      <div class="c-card-grid__cards">
        @foreach ($relatedPosts as $card)
          <x-card
            card-style="news"
            :image="$card['image']"
            :date="$card['date']"
            :heading="$card['heading']"
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
        href="{{ get_option('page_for_posts') ? get_permalink(get_option('page_for_posts')) : home_url('/') }}"
      >
        {{ __('More news', 'sage') }}
        <x-icon name="arrow-right" slot="end" />
      </wa-button>
    </div>
  @endif

  @php(comments_template())
</article>
