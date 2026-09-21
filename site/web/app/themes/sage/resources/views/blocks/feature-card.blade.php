<section {{ $attributes->class(['c-feature-card']) }}>

<div class="o-container">
  <x-card
    :card-style="$cardStyle"
    :image="$image"
    :date="$date"
    :heading="$heading"
    :body="$body"
    :link="$link"
    :cta-style="$ctaStyle"
  />
</div>

</section>
