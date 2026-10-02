// Sticky site header, auto-hide on scroll: slides out of view scrolling
// down, slides back in scrolling up, so it doesn't permanently eat
// viewport space while still being reachable.
const SCROLL_DELTA_THRESHOLD = 8; // px of movement before a direction change counts, so trackpad/momentum jitter near a reversal doesn't flicker the header

const header = document.querySelector('.c-site-header');

if (header) {
  let lastScrollY = window.scrollY;
  let ticking = false;

  // Published as --site-header-height so anything that needs to sit below
  // the sticky header (e.g. a sticky sidebar, or a heading's scroll-margin
  // for anchor jumps) can offset against its real height instead of a
  // guessed constant — the header's own height doesn't change when it
  // hides, only its transform, so this only needs recalculating on resize.
  const publishHeaderHeight = () => {
    document.documentElement.style.setProperty('--site-header-height', `${header.offsetHeight}px`);
  };

  publishHeaderHeight();
  window.addEventListener('resize', publishHeaderHeight);

  // While hidden, the header is also marked inert/aria-hidden so assistive
  // tech and keyboard focus stay in sync with what's visually present
  // (WCAG 4.1.2) — a sighted keyboard user can't tab into controls that
  // are currently translated off-screen.
  //
  // Also toggled on <html> as .is-header-hidden — a plain state class, not
  // a measured height or custom property — for anything elsewhere that
  // needs to know whether the header is currently covering the top of the
  // viewport (e.g. the sticky nav template's `top` offset).
  const setHidden = (hidden) => {
    header.classList.toggle('is-hidden', hidden);
    header.toggleAttribute('inert', hidden);
    header.setAttribute('aria-hidden', String(hidden));
    document.documentElement.classList.toggle('is-header-hidden', hidden);
  };

  const updateVisibility = () => {
    const currentScrollY = Math.max(window.scrollY, 0);
    const delta = currentScrollY - lastScrollY;

    if (currentScrollY <= header.offsetHeight) {
      setHidden(false);
      lastScrollY = currentScrollY;
    } else if (delta > SCROLL_DELTA_THRESHOLD) {
      setHidden(true);
      lastScrollY = currentScrollY;
    } else if (delta < -SCROLL_DELTA_THRESHOLD) {
      setHidden(false);
      lastScrollY = currentScrollY;
    }
  };

  window.addEventListener(
    'scroll',
    () => {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(() => {
        updateVisibility();
        ticking = false;
      });
    },
    { passive: true },
  );
}
