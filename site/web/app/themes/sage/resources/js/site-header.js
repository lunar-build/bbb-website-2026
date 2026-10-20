// Sticky header: slides out scrolling down, back in scrolling up.
const SCROLL_DELTA_THRESHOLD = 8; // px before a direction change counts (avoids trackpad jitter flicker)

const header = document.querySelector('.c-site-header');

// Lets other modules (e.g. sticky-nav-template.js) force the header visible before
// a programmatic scroll. No-op without a header.
export let setHeaderHidden = () => {};

if (header) {
  // Real header height (varies by breakpoint) as a CSS var for sticky elements below it.
  const updateHeaderHeightVar = () => {
    document.documentElement.style.setProperty('--site-header-height', `${header.offsetHeight}px`);
  };

  updateHeaderHeightVar();
  new ResizeObserver(updateHeaderHeightVar).observe(header);

  let lastScrollY = window.scrollY;
  let ticking = false;

  // While hidden the header is inert/aria-hidden so focus and AT match what's visible
  // (WCAG 4.1.2). Also sets .is-header-hidden on <html> for offsets elsewhere
  // (e.g. the sticky nav template's `top`).
  const setHidden = (hidden) => {
    header.classList.toggle('is-hidden', hidden);
    header.toggleAttribute('inert', hidden);
    header.setAttribute('aria-hidden', String(hidden));
    document.documentElement.classList.toggle('is-header-hidden', hidden);
  };

  setHeaderHidden = setHidden;

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
