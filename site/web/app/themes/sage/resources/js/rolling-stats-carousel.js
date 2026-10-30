// Odometer count-up (wa-carousel has none): the number counts from 0 to its value, and
// each digit is a "reel" (0-9 then 0, see the Blade view) that translateY()s to show it.
// aria-hidden — the Blade view's sr-only sentence carries the real value.
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

const DURATION = 1700;
const easeOutCubic = (t) => 1 - (1 - t) ** 3;
const frames = new WeakMap();

// Reels right-to-left with their place value, in units of the smallest decimal place.
const readReels = (value) => {
  const parts = [...value.children];
  const reels = parts.filter((el) => el.matches('[data-rolling-stats-reel]'));
  const point = parts.findIndex((el) => el.classList.contains('c-rolling-stats-carousel__decimal'));
  const decimals = point === -1 ? 0 : parts.length - 1 - point;

  return {
    target: parseInt(reels.map((el) => el.dataset.digit).join(''), 10) || 0,
    reels: reels.reverse().map((el, place) => ({
      el,
      place,
      track: el.querySelector('.c-rolling-stats-carousel__reel-track'),
      // The integer ones digit and anything after the point always show ("0", "0.0").
      leading: place > decimals,
    })),
  };
};

// Each digit rolls with the one below it only while that one passes 9 → 0, like a real odometer.
const render = (reels, v) => {
  let below = 0;

  reels.forEach((reel) => {
    const unit = 10 ** reel.place;
    const pos = reel.place === 0 ? v % 10 : (Math.floor(v / unit) % 10) + Math.max(0, below - 9);
    below = pos;

    reel.track.style.transform = `translateY(${-pos}em)`;

    if (reel.leading) {
      // Unreached leading digits are collapsed, then grow in as they roll from 0 to 1.
      const shown = v >= unit ? 1 : pos;
      reel.el.style.opacity = shown;
      reel.el.style.width = `${reel.width * shown}px`;
    }
  });
};

const countUp = (item) => {
  const value = item.querySelector('[data-rolling-stats-value]');
  if (!value) return;

  cancelAnimationFrame(frames.get(item));

  const { target, reels } = readReels(value);
  reels.forEach((reel) => {
    reel.el.style.width = '';
    reel.el.style.opacity = '';
    reel.width = reel.el.offsetWidth;
  });

  if (reducedMotion.matches) {
    render(reels, target);
    return;
  }

  const start = performance.now();
  const tick = (now) => {
    const progress = Math.min(1, (now - start) / DURATION);
    render(reels, target * easeOutCubic(progress));

    if (progress < 1) {
      frames.set(item, requestAnimationFrame(tick));
    } else {
      render(reels, target);
      reels.forEach((reel) => {
        reel.el.style.width = '';
        reel.el.style.opacity = '';
      });
    }
  };

  render(reels, 0);
  frames.set(item, requestAnimationFrame(tick));
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

  // A looping wa-carousel scrolls onto cloned slides (data-clone="<index>") when
  // wrapping — animate every copy or the clone sits at its final number.
  const animateSlide = (index) => {
    if (typeof index !== 'number') return;

    countUp(items[index]);
    carousel.querySelectorAll(`wa-carousel-item[data-clone="${index}"]`).forEach(countUp);
  };

  carousel.addEventListener('wa-slide-change', (event) => animateSlide(event.detail?.index));

  // Count up on scroll into view, not page load — below-the-fold blocks finished off-screen.
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
