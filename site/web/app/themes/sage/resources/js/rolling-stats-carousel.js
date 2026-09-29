// Bespoke slot-counter — WA's wa-carousel has no counting feature. Each
// digit is a "reel" (0-9 strip repeated 3x, see the component Blade view)
// that translateY()s to its target row. Presentational only — aria-hidden,
// see the sr-only sentence in the Blade view for the real value.
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

const SET_SIZE = 10; // digits 0-9 per repeated set in the reel track
const LANDING_SET = 2; // land in the 3rd (last) repeated set, so every reel travels the same distance regardless of its target digit
const DURATION = 900;
const STAGGER = 70; // ms delay added per reel index, left-to-right cascade

const spinReel = (reel, index) => {
  const track = reel.querySelector('.c-rolling-stats-carousel__reel-track');
  if (!track) return;

  const digit = parseInt(reel.dataset.digit || '0', 10);

  if (reducedMotion.matches) {
    track.style.transition = 'none';
    track.style.transform = `translateY(${-digit}em)`;
    return;
  }

  // Reset to row 0 (digit '0') before every spin, so a revisited slide
  // counts up from 0 again instead of starting on its target digit.
  track.style.transition = 'none';
  track.style.transform = 'translateY(0)';
  void track.offsetHeight; // force reflow so the reset isn't animated

  const target = LANDING_SET * SET_SIZE + digit;

  requestAnimationFrame(() => {
    track.style.transition = `transform ${DURATION}ms cubic-bezier(0.22, 1, 0.36, 1) ${index * STAGGER}ms`;
    track.style.transform = `translateY(${-target}em)`;
  });

  track.addEventListener(
    'transitionend',
    () => {
      // Back to row 0's set (visually identical digit) so the next spin has room to travel.
      track.style.transition = 'none';
      track.style.transform = `translateY(${-digit}em)`;
    },
    { once: true }
  );
};

// WA stretches every wa-carousel-item to one shared box (--aspect-ratio,
// see component SCSS) — measure each slide's real content height instead
// and size the carousel to the tallest, so no slide's copy gets clipped.
const syncCarouselHeight = (wrapper, carousel) => {
  const contents = carousel.querySelectorAll('.c-rolling-stats-carousel__content');
  if (!contents.length) return;

  // .content itself isn't stretched (only its wa-carousel-item ancestor is), so
  // offsetHeight here is each slide's true natural height regardless of the box applied.
  const tallestContent = Math.max(...[...contents].map((el) => el.offsetHeight));

  const item = carousel.querySelector('wa-carousel-item');
  const itemPadding = item
    ? parseFloat(getComputedStyle(item).paddingTop) + parseFloat(getComputedStyle(item).paddingBottom)
    : 0;

  wrapper.style.setProperty('--rolling-stats-carousel-height', `${tallestContent + itemPadding}px`);
};

document.querySelectorAll('[data-rolling-stats-carousel]').forEach((wrapper) => {
  const carousel = wrapper.querySelector('wa-carousel');
  if (!carousel) return;

  const items = carousel.querySelectorAll('wa-carousel-item');

  const animateSlide = (item) => {
    const reels = item?.querySelectorAll('[data-rolling-stats-reel]');
    if (!reels) return;

    reels.forEach((reel, index) => spinReel(reel, index));
  };

  // Spin the initially-visible slide too, not just later wa-slide-change events.
  animateSlide(items[0]);

  carousel.addEventListener('wa-slide-change', (event) => {
    const index = event.detail?.index;
    animateSlide(typeof index === 'number' ? items[index] : null);
  });

  syncCarouselHeight(wrapper, carousel);

  // Re-measure on resize (clamped font-size + wrapping both change with viewport
  // width) and once more on load in case web fonts swap in and shift line heights.
  let resizeFrame;
  window.addEventListener('resize', () => {
    cancelAnimationFrame(resizeFrame);
    resizeFrame = requestAnimationFrame(() => syncCarouselHeight(wrapper, carousel));
  });
  window.addEventListener('load', () => syncCarouselHeight(wrapper, carousel));
});
