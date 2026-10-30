{{-- Figma "Sample page -> Case Study": breadcrumbs + title, hero photo, Q&A rows, other case studies. --}}
@php($breadcrumbs = $breadcrumbs())
@php($title = $title())
@php($details = $details())
@php($relatedCards = $relatedCards())

<article @php(post_class('h-entry o-container--narrow'))>
  {!! \App\Support\ContentHero::fallback($title, $breadcrumbs) !!}

  @if (has_post_thumbnail())
    <div class="o-container">
      <div class="c-single-case-study__image">
        {!! get_the_post_thumbnail(null, 'large', ['class' => 'c-single-case-study__image-el']) !!}
      </div>
    </div>
  @endif

  @if (count($details['rows']))
    <dl class="o-container c-single-case-study__details">
      @foreach ($details['rows'] as $row)
        <div class="c-single-case-study__row">
          <dt class="c-single-case-study__question">{{ $row['question'] }}</dt>
          <dd class="c-single-case-study__answer">{!! $row['answer'] !!}</dd>
        </div>
      @endforeach
    </dl>
  @elseif ($details['body'])
    <div class="o-container e-content">
      {!! $details['body'] !!}
    </div>
  @endif

  @if (count($relatedCards))
    <div class="c-single-case-study__related">
      @include('blocks.case-study-grid', [
          'attributes' => new \Illuminate\View\ComponentAttributeBag(),
          'cards' => $relatedCards,
      ])
    </div>
  @endif
</article>
