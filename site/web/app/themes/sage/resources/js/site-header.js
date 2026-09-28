// Sticky site header, auto-hide on scroll: slides out of view scrolling
// down, slides back in scrolling up, so it doesn't permanently eat
// viewport space while still being reachable.
const SCROLL_DELTA_THRESHOLD = 8; // px of movement before a direction change counts, so trackpad/momentum jitter near a reversal doesn't flicker the header

const header = document.querySelector('.c-site-header');

if (header) {
  // Exposes the header's real rendered height (it now includes the desktop
  // nav row, not just the logo/CTA row, and varies by breakpoint) as a CSS
  // var, so anything sticky further down the page (e.g. the pattern
  // library nav) can offset itself below it instead of a guessed constant.
  const updateHeaderHeightVar = () => {
    document.documentElement.style.setProperty('--site-header-height', `${header.offsetHeight}px`);
  };

  updateHeaderHeightVar();
  new ResizeObserver(updateHeaderHeightVar).observe(header);

  let lastScrollY = window.scrollY;
  let ticking = false;

  // While hidden, the header is also marked inert/aria-hidden so assistive
  // tech and keyboard focus stay in sync with what's visually present
  // (WCAG 4.1.2) — a sighted keyboard user can't tab into controls that
  // are currently translated off-screen.
  const setHidden = (hidden) => {
    header.classList.toggle('is-hidden', hidden);
    header.toggleAttribute('inert', hidden);
    header.setAttribute('aria-hidden', String(hidden));
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
