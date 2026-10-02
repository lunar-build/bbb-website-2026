/**
 * Behaviour for the Sticky Nav page template
 * (resources/views/template-sticky-nav.blade.php): highlights the current
 * section in the left-rail page menu as the reader scrolls, and moves a
 * marker alongside the rail to sit level with whichever item is active.
 *
 * The page menu itself (resources/views/components/sticky-page-menu.blade.php)
 * only renders a static list server-side — this is the "scroll-spy" half
 * its own doc comment defers to whichever template uses it.
 */
const nav = document.querySelector('[data-sticky-nav-menu]');

if (nav) {
  const links = Array.from(nav.querySelectorAll('.c-sticky-page-menu__link'));
  const track = document.querySelector('[data-sticky-nav-progress]');
  const marker = document.querySelector('[data-sticky-nav-progress-marker]');

  const sections = links
    .map((link) => {
      const id = link.getAttribute('href')?.replace(/^#/, '');

      return id ? document.getElementById(id) : null;
    })
    .filter(Boolean);

  // Positions the marker level with the given nav item by comparing its
  // on-screen position to the track's — not a scroll-fraction fill, a
  // snap-to-item indicator (items and the track are siblings, not nested,
  // so this is measured rather than assumed from layout).
  const moveMarkerTo = (item) => {
    if (!track || !marker || !item) {
      return;
    }

    const trackRect = track.getBoundingClientRect();
    const itemRect = item.getBoundingClientRect();

    marker.style.transform = `translateY(${itemRect.top - trackRect.top}px)`;
    marker.style.height = `${itemRect.height}px`;
    marker.style.opacity = '1';
  };

  const setActiveLink = (id) => {
    links.forEach((link) => {
      const isActive = link.getAttribute('href') === `#${id}`;
      const item = link.closest('.c-sticky-page-menu__item');

      item?.classList.toggle('c-sticky-page-menu__item--active', isActive);

      if (isActive) {
        link.setAttribute('aria-current', 'location');
        moveMarkerTo(item);
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

  window.addEventListener('resize', () => {
    const activeItem = nav.querySelector('.c-sticky-page-menu__item--active');

    moveMarkerTo(activeItem);
  });
}
