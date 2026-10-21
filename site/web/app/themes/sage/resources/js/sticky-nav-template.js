// Scroll-spy + click handling for components/sticky-page-menu.blade.php. Clicks
// are handled in JS because the native #anchor jump wasn't reliably scrolling.
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

  // True during a programmatic scroll: otherwise the observer re-fires mid-animation
  // and overrides the just-clicked item (a short first section settles with the
  // viewport centre past it, in the second).
  let suppressObserver = false;
  let suppressObserverTimeout = null;

  const scrollToSection = (id, behavior, moveFocus = false) => {
    const target = document.getElementById(id);

    if (!target) {
      return;
    }

    suppressObserver = true;
    window.clearTimeout(suppressObserverTimeout);

    // Show the header *before* scrolling: scroll-margin-top is read once at call time,
    // so jumping up from a header-hidden position would lock in the smaller offset
    // and land the heading under the header when it reappears.
    if (target.getBoundingClientRect().top < 0) {
      setHeaderHidden(false);
    }

    target.scrollIntoView({ behavior, block: 'start' });
    setActiveLink(id);

    // Click only: focus the heading (tabindex="-1", see app/filters.php) so the next
    // Tab continues into that section (WCAG 2.4.3).
    if (moveFocus) {
      target.focus({ preventScroll: true });
    }

    // Timeout fallback for browsers without scrollend (Safari < 17.4).
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

  if (links[0]) {
    const firstId = links[0].getAttribute('href')?.replace(/^#/, '');

    if (firstId) {
      setActiveLink(firstId);
    }
  }

  // Native fragment scroll fires before the hero image loads and reflows the page.
  const initialHashId = location.hash.replace(/^#/, '');

  if (initialHashId && document.getElementById(initialHashId)) {
    window.addEventListener('load', () => scrollToSection(initialHashId, 'auto'));
  }

  if (sections.length && 'IntersectionObserver' in window) {
    // rootMargin shrinks the root to a 1px line at viewport centre — symmetric for
    // both scroll directions.
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
