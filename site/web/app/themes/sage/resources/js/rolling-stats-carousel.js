// Rolling Stats Carousel's count-up animation. Not a Web Awesome feature —
// wa-carousel only gives us the `wa-slide-change` event and
// goToSlide()/next()/previous(); animating the number itself is entirely
// bespoke. The displayed number is aria-hidden (see the block's Blade
// view) — this script only ever changes presentational textContent, never
// anything screen readers announce, so there's no need for an aria-live
// region here.
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

const runningAnimations = new WeakMap();

const animateValue = (el, target, decimals, duration = 1200) => {
  const previousFrame = runningAnimations.get(el);
  if (previousFrame) cancelAnimationFrame(previousFrame);

  if (reducedMotion.matches) {
    el.textContent = target.toFixed(decimals);
    return;
  }

  const start = performance.now();

  const step = (now) => {
    const progress = Math.min((now - start) / duration, 1);
    const eased = 1 - (1 - progress) ** 3; // ease-out cubic
    el.textContent = (target * eased).toFixed(decimals);

    if (progress < 1) {
      runningAnimations.set(el, requestAnimationFrame(step));
    } else {
      runningAnimations.delete(el);
      el.textContent = target.toFixed(decimals);
    }
  };

  runningAnimations.set(el, requestAnimationFrame(step));
};

document.querySelectorAll('[data-rolling-stats-carousel]').forEach((wrapper) => {
  const carousel = wrapper.querySelector('wa-carousel');
  if (!carousel) return;

  const items = carousel.querySelectorAll('wa-carousel-item');

  const animateSlide = (item) => {
    const valueEl = item?.querySelector('[data-rolling-stats-value]');
    if (!valueEl) return;

    const target = parseFloat(valueEl.dataset.target || '0');
    const decimals = parseInt(valueEl.dataset.decimals || '0', 10);

    animateValue(valueEl, target, decimals);
  };

  // Count the initially-visible slide up on page load too, not just on
  // subsequent wa-slide-change events.
  animateSlide(items[0]);

  carousel.addEventListener('wa-slide-change', (event) => {
    const index = event.detail?.index;
    animateSlide(typeof index === 'number' ? items[index] : null);
  });
});
