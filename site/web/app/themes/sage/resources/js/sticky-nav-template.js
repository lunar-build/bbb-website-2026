// Scroll-spy + click handling for the Sticky Nav page menu
// (resources/views/components/sticky-page-menu.blade.php) — that component
// only renders the static list; active-state/track styling is CSS, this
// just toggles the class and handles clicks (native #anchor jump wasn't
// reliably scrolling in testing).
import { setHeaderHidden } from './site-header.js';

const nav = document.querySelector('[data-sticky-nav-menu]');

if (nav) {
  const links = Array.from(nav.querySelectorAll('.c-sticky-page-menu__link'));
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const sections = links
    .map((link) => {
      const id = link.getAttribute('href')?.replace(/^#/, '');

      return id ? document.getElementById(id) : null;
    })
    .filter(Boolean);

  const setActiveLink = (id) => {
    links.forEach((link) => {
      const isActive = link.getAttribute('href') === `#${id}`;
      const item = link.closest('.c-sticky-page-menu__item');

      item?.classList.toggle('c-sticky-page-menu__item--active', isActive);

      if (isActive) {
        link.setAttribute('aria-current', 'location');
      } else {
        link.removeAttribute('aria-current');
      }
    });
  };

  // Set true while a programmatic scroll is in flight: the IntersectionObserver below
  // otherwise re-fires mid-animation (and once more on settling) and can reassign active
  // state to whichever section ends up centered, overriding the just-clicked item — e.g.
  // a short first section scrolled to from the bottom of the page settles with the
  // viewport's center past it, into the second section.
  let suppressObserver = false;
  let suppressObserverTimeout = null;

  const scrollToSection = (id, behavior, moveFocus = false) => {
    const target = document.getElementById(id);

    if (!target) {
      return;
    }

    suppressObserver = true;
    window.clearTimeout(suppressObserverTimeout);

    // Scrolling up always reveals the header once movement settles (site-header.js's
    // own scroll-direction logic) — force it visible *before* scrollIntoView runs,
    // since scroll-margin-top is read once at call time: without this, a jump up from
    // a scrolled-down (header-hidden) position locks in the smaller hidden-state
    // offset, and the heading lands under the header once it reappears mid-scroll.
    if (target.getBoundingClientRect().top < 0) {
      setHeaderHidden(false);
    }

    // block: 'start' respects the target's scroll-margin-top so it clears the header.
    target.scrollIntoView({ behavior, block: 'start' });
    setActiveLink(id);

    // Only on a real click — the heading (tabindex="-1", see app/filters.php) takes
    // keyboard focus so the next Tab continues into the section just jumped to, instead
    // of staying on the nav link (WCAG 2.4.3). preventScroll since scrollIntoView above
    // already handled it.
    if (moveFocus) {
      target.focus({ preventScroll: true });
    }

    // 'scrollend' fires once the scroll genuinely settles, however long the animation
    // takes; timeout fallback for browsers without it (Safari < 17.4).
    if ('onscrollend' in window) {
      window.addEventListener('scrollend', () => { suppressObserver = false; }, { once: true });
    } else {
      suppressObserverTimeout = window.setTimeout(() => { suppressObserver = false; }, 1000);
    }
  };

  links.forEach((link) => {
    link.addEventListener('click', (event) => {
      const id = link.getAttribute('href')?.replace(/^#/, '');

      if (!id || !document.getElementById(id)) {
        return;
      }

      event.preventDefault();
      scrollToSection(id, prefersReducedMotion ? 'auto' : 'smooth', true);
      history.pushState(null, '', `#${id}`);
    });
  });

  // Default the first item active on load, before any scroll/intersection fires.
  if (links[0]) {
    const firstId = links[0].getAttribute('href')?.replace(/^#/, '');

    if (firstId) {
      setActiveLink(firstId);
    }
  }

  // A direct #section URL load gets the browser's native fragment scroll before the
  // Image Hero above it finishes loading and reflows the page — re-run once loaded.
  const initialHashId = location.hash.replace(/^#/, '');

  if (initialHashId && document.getElementById(initialHashId)) {
    window.addEventListener('load', () => scrollToSection(initialHashId, 'auto'));
  }

  if (sections.length && 'IntersectionObserver' in window) {
    // rootMargin shrinks the root to a 1px line at viewport center — symmetric
    // trigger for both scroll directions, unlike a top-biased zone.
    const observer = new IntersectionObserver(
      (entries) => {
        if (suppressObserver) {
          return;
        }

        const visible = entries
          .filter((entry) => entry.isIntersecting)
          .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);

        if (visible[0]) {
          setActiveLink(visible[0].target.id);
        }
      },
      { rootMargin: '-50% 0px -50% 0px', threshold: 0 }
    );

    sections.forEach((section) => observer.observe(section));
  }
}
