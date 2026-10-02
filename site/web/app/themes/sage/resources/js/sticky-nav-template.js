/**
 * Behaviour for the Sticky Nav page template
 * (resources/views/template-sticky-nav.blade.php): highlights the current
 * section in the left-rail page menu as the reader scrolls.
 *
 * The page menu itself (resources/views/components/sticky-page-menu.blade.php)
 * already renders the track and the active item's highlight in CSS
 * (.c-sticky-page-menu__list::before + .c-sticky-page-menu__item--active) —
 * this only has to toggle that active class as the reader scrolls, nothing
 * needs positioning in JS.
 */
const nav = document.querySelector('[data-sticky-nav-menu]');

if (nav) {
  const links = Array.from(nav.querySelectorAll('.c-sticky-page-menu__link'));

  const sections = links
    .map((link) => {
      const id = link.getAttribute('href')?.replace(/^#/, '');

      return id ? document.getElementById(id) : null;
    })
    .filter(Boolean);

  const setActiveLink = (id) => {
    links.forEach((link) => {
      const isActive = link.getAttribute('href') === `#${id}`;
      const item = link.closest('.c-sticky-page-menu__item');

      item?.classList.toggle('c-sticky-page-menu__item--active', isActive);

      if (isActive) {
        link.setAttribute('aria-current', 'location');
      } else {
        link.removeAttribute('aria-current');
      }
    });
  };

  if (sections.length && 'IntersectionObserver' in window) {
    // Treat a section as "current" once it's crossed into the top ~30% of
    // the viewport — picks the top-most section still above that line
    // rather than whichever fires its observer callback last.
    const observer = new IntersectionObserver(
      (entries) => {
        const visible = entries
          .filter((entry) => entry.isIntersecting)
          .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);

        if (visible[0]) {
          setActiveLink(visible[0].target.id);
        }
      },
      { rootMargin: '0px 0px -70% 0px', threshold: 0 }
    );

    sections.forEach((section) => observer.observe(section));
  }
}
