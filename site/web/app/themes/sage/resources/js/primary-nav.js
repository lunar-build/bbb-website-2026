// Mobile nav overlay (#primary-menu, sections/primary-nav.blade.php):
// open/close via .c-header-menu-toggle, plus panel drill-in/back between
// the root L1 list and each L1 item's own level-2 panel.
const toggle = document.querySelector('.c-header-menu-toggle');
const menu = document.getElementById('primary-menu');
const siteHeader = document.querySelector('lunar-site-header');

if (toggle && menu) {
  const panels = menu.querySelectorAll('.c-primary-nav__panel');

  // The overlay's own top padding is a fixed value, not "however tall the
  // header happens to be" — without this, its content starts under that
  // fixed padding while the header (higher z-index, painted on top for
  // its own real height) can cover more or less than that, hiding
  // whichever bit of content falls in the gap. Measuring on each open
  // keeps it correct if the header's height ever changes.
  const syncHeaderHeight = () => {
    if (!siteHeader) return;
    document.documentElement.style.setProperty('--header-height', `${siteHeader.getBoundingClientRect().height}px`);
  };

  // Moving focus onto the newly-shown panel isn't just nice-to-have: the
  // element that was focused when its panel got hidden doesn't stay
  // focused — the browser drops focus to <body> (on the next tick, not
  // synchronously). Since <body> isn't tracked by the Tab trap below,
  // leaving it there would let Tab escape the overlay entirely after any
  // drill-in/back. Focusing the panel's first focusable element (its Back
  // button for a level-2 panel) keeps the trap intact and gives keyboard
  // users a sensible landing point.
  const showPanel = (name) => {
    panels.forEach((panel) => {
      panel.hidden = panel.dataset.panel !== name;
    });
    menu.querySelector('.c-primary-nav__panel:not([hidden])')?.querySelector('a[href], button:not([disabled])')?.focus();
  };

  const setOpen = (open) => {
    toggle.setAttribute('aria-expanded', String(open));
    toggle.setAttribute('aria-label', open ? 'Close menu' : 'Menu');
    menu.hidden = !open;

    // Locks the page (not just the overlay) from scrolling while open —
    // without this, scrolling inside the overlay past its own content (or
    // via a trackpad/wheel event that doesn't hit its overflow) reaches
    // the page underneath, which triggers lunar-site-header's own
    // scroll-hide-on-scroll-down behaviour and makes the header vanish
    // behind the overlay's lower z-index.
    document.documentElement.style.overflow = open ? 'hidden' : '';

    if (open) {
      syncHeaderHeight();
      showPanel('root');
      // Only one full-screen mobile overlay makes sense open at a time.
      document.getElementById('mobile-search')?.setAttribute('hidden', '');
      document.querySelector('.c-header-search-toggle')?.setAttribute('aria-expanded', 'false');
    }
  };

  toggle.addEventListener('click', () => {
    setOpen(toggle.getAttribute('aria-expanded') !== 'true');
  });

  menu.addEventListener('click', (event) => {
    const drill = event.target.closest('[data-open-panel]');
    if (drill) {
      showPanel(drill.dataset.openPanel);
      menu.scrollTo(0, 0);
      return;
    }

    if (event.target.closest('[data-back-panel]')) {
      showPanel('root');
      menu.scrollTo(0, 0);
      return;
    }

    // A leaf link (real navigation) — close the overlay rather than
    // leaving it open. Matters for same-page anchors too, not just
    // placeholder `#` hrefs, since those don't trigger a page load that
    // would otherwise leave it open with nothing to see.
    if (event.target.closest('.c-primary-nav__link:not([data-open-panel])')) {
      setOpen(false);
    }
  });

  // Focus trap: the toggle button is the close control, but it lives in
  // .c-header-top, not inside #primary-menu itself — so it's treated as
  // the start/end of the trapped sequence explicitly rather than relying
  // on it being a DOM descendant of the menu. Recomputed on every Tab
  // press (not cached) since which panel is visible changes on drill-in/
  // back, changing what's focusable.
  const getTrapElements = () => {
    const panel = menu.querySelector('.c-primary-nav__panel:not([hidden])');
    const panelFocusables = panel ? [...panel.querySelectorAll('a[href], button:not([disabled])')] : [];
    return [toggle, ...panelFocusables];
  };

  document.addEventListener('keydown', (event) => {
    if (menu.hidden) return;

    if (event.key === 'Escape') {
      setOpen(false);
      toggle.focus();
      return;
    }

    if (event.key !== 'Tab') return;
    const trap = getTrapElements();
    if (!trap.length) return;
    const first = trap[0];
    const last = trap[trap.length - 1];

    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  });
}
