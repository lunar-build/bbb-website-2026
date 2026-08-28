{{-- Repeated at the bottom of every mobile nav panel (root + each level-2
     drill-in), per Figma's "Menu level 1"/"Menu level 2" states. --}}
@if (! empty($cta['url']))
  <wa-button variant="brand" appearance="accent" pill with-end class="c-header-cta c-primary-nav__cta" href="{{ $cta['url'] }}"
    @if (($cta['target'] ?? '') === '_blank') target="_blank" rel="noopener" @endif>
    {{ $cta['title'] }}
    <x-icon name="arrow-right" slot="end" />
  </wa-button>
@endif

{{-- Same icons as the desktop top-bar (facebook/instagram/x, currentColor)
     — here just given a circular blue-medium badge via CSS rather than a
     separate baked asset, per Figma's mobile-menu social treatment. --}}
@if (! empty($socialLinks))
  <ul class="c-primary-nav__socials">
    @foreach ($socialLinks as $link)
      <li>
        <a href="{{ $link['url'] }}" class="c-primary-nav__social" aria-label="{{ ucfirst($link['platform']) }}"
          target="_blank" rel="noopener noreferrer">
          <x-icon name="{{ $link['platform'] }}" />
        </a>
      </li>
    @endforeach
  </ul>
@endif
