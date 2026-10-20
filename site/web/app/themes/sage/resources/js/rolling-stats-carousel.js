// Slot-counter (wa-carousel has none): each digit is a "reel" (0-9 strip repeated 3x,
// see the Blade view) that translateY()s to its target row. aria-hidden — the
// Blade view's sr-only sentence carries the real value.
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

  // Reset to row 0 so a revisited slide counts up from 0 again.
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

// WA stretches every item to one --aspect-ratio box; size the carousel to the
// tallest slide's real content instead so no copy is clipped.
const syncCarouselHeight = (wrapper, carousel) => {
  const contents = carousel.querySelectorAll('.c-rolling-stats-carousel__content');
  if (!contents.length) return;

  // .content isn't stretched (only its wa-carousel-item is), so this is the natural height.
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

  const spinItem = (item) => {
    item.querySelectorAll('[data-rolling-stats-reel]').forEach((reel, index) => spinReel(reel, index));
  };

  // A looping wa-carousel scrolls onto cloned slides (data-clone="<index>") when
  // wrapping — spin every copy or the clone sits at its final number.
  const animateSlide = (index) => {
    if (typeof index !== 'number') return;

    spinItem(items[index]);
    carousel.querySelectorAll(`wa-carousel-item[data-clone="${index}"]`).forEach(spinItem);
  };

  carousel.addEventListener('wa-slide-change', (event) => animateSlide(event.detail?.index));

  // Spin on scroll into view, not page load — below-the-fold blocks spun off-screen.
  const observer = new IntersectionObserver(
    (entries) => {
      if (!entries[0].isIntersecting) return;
      animateSlide(0);
      observer.disconnect();
    },
    { threshold: 0.5 }
  );
  observer.observe(wrapper);

  syncCarouselHeight(wrapper, carousel);

  // Re-measure on resize (fluid font-size/wrapping) and on load (web font swap).
  let resizeFrame;
  window.addEventListener('resize', () => {
    cancelAnimationFrame(resizeFrame);
    resizeFrame = requestAnimationFrame(() => syncCarouselHeight(wrapper, carousel));
  });
  window.addEventListener('load', () => syncCarouselHeight(wrapper, carousel));
});
