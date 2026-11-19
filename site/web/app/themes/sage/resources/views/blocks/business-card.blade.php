<x-block-root :heading="(bool) $name" {{ $attributes->class(['c-business-card']) }}>

@if ($name)
<div class="o-container">
  <x-business-card
    :name="$name"
    :contact-info="$contactInfo"
    :services="$services"
    :description="$description"
    :link="$link"
    :cta-colour="$ctaColour"
    :heading-level="$headingLevel"
  />
</div>
@endif

</x-block-root>
