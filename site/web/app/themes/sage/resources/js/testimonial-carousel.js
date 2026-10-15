// wa-carousel only builds its loop clones once, in connectedCallback — it
// doesn't regenerate them when slidesPerPage changes afterwards, so simply
// mutating the attribute on a live instance leaves a stale/mismatched
// clone set. Instead we rebuild the element from scratch on each
// breakpoint change, so it goes through a normal fresh connect every time.
// (Equal card heights across visible slides are handled in CSS — see
// _testimonial-card-carousel.scss's ::part(scroll-container) rule — not
// here.)
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
