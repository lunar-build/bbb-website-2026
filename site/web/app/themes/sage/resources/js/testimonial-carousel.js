// wa-carousel builds its loop clones once, in connectedCallback, so changing
// slidesPerPage on a live instance leaves stale clones — rebuild the element on each
// breakpoint change instead. (Equal card heights are CSS: see
// _testimonial-card-carousel.scss.)
const desktop = window.matchMedia('(min-width: 64rem)'); // bp.$lg

document.querySelectorAll('[data-testimonial-carousel]').forEach((carousel) => {
  const template = carousel.cloneNode(true);
  let current = carousel;

  const applySlidesPerPage = () => {
    const slidesPerPage = desktop.matches ? 2 : 1;
    const next = template.cloneNode(true);
    next.setAttribute('slides-per-page', slidesPerPage);
    next.setAttribute('slides-per-move', slidesPerPage);
    current.replaceWith(next);
    current = next;
  };

  applySlidesPerPage();
  desktop.addEventListener('change', applySlidesPerPage);
});
