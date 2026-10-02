// Sticky site header, auto-hide on scroll: slides out of view scrolling
// down, slides back in scrolling up, so it doesn't permanently eat
// viewport space while still being reachable.
const SCROLL_DELTA_THRESHOLD = 8; // px of movement before a direction change counts, so trackpad/momentum jitter near a reversal doesn't flicker the header

const header = document.querySelector('.c-site-header');

if (header) {
  let lastScrollY = window.scrollY;
  let ticking = false;

  // Published as --site-header-height so anything that needs to sit below
  // the sticky header (e.g. a heading's scroll-margin for anchor jumps,
  // which should clear the header's full height even if it happens to be
  // hidden at the moment) can offset against its real height instead of a
  // guessed constant — the header's own height doesn't change when it
  // hides, only its transform, so this only needs recalculating on resize.
  //
  // --site-header-offset tracks the header's current *visible* height
  // instead — 0 while hidden, the full height while shown — for anything
  // that should visually track the header's position (e.g. a sticky
  // sidebar settling higher once the header slides away), transitioning
  // alongside it rather than leaving a gap where the header used to be.
  let headerHeight = header.offsetHeight;

  const publishHeaderOffset = (hidden) => {
    document.documentElement.style.setProperty('--site-header-offset', hidden ? '0px' : `${headerHeight}px`);
  };

  const publishHeaderHeight = () => {
    headerHeight = header.offsetHeight;
    document.documentElement.style.setProperty('--site-header-height', `${headerHeight}px`);
    publishHeaderOffset(header.classList.contains('is-hidden'));
  };

  publishHeaderHeight();
  window.addEventListener('resize', publishHeaderHeight);

  // While hidden, the header is also marked inert/aria-hidden so assistive
  // tech and keyboard focus stay in sync with what's visually present
  // (WCAG 4.1.2) — a sighted keyboard user can't tab into controls that
  // are currently translated off-screen.
  const setHidden = (hidden) => {
    header.classList.toggle('is-hidden', hidden);
    header.toggleAttribute('inert', hidden);
    header.setAttribute('aria-hidden', String(hidden));
    publishHeaderOffset(hidden);
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
