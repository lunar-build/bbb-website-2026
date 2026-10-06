// Scroll-spy + click handling for the Sticky Nav page menu
// (resources/views/components/sticky-page-menu.blade.php) — that component
// only renders the static list; active-state/track styling is CSS, this
// just toggles the class and handles clicks (native #anchor jump wasn't
// reliably scrolling in testing).
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

  const scrollToSection = (id, behavior) => {
    const target = document.getElementById(id);

    if (!target) {
      return;
    }

    // block: 'start' respects the target's scroll-margin-top so it clears the header.
    target.scrollIntoView({ behavior, block: 'start' });
    setActiveLink(id);
  };

  links.forEach((link) => {
    link.addEventListener('click', (event) => {
      const id = link.getAttribute('href')?.replace(/^#/, '');

      if (!id || !document.getElementById(id)) {
        return;
      }

      event.preventDefault();
      scrollToSection(id, prefersReducedMotion ? 'auto' : 'smooth');
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
