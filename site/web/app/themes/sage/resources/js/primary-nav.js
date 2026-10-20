// Mobile nav overlay: open/close, plus drill-in/back between the L1 list and level-2 panels.
const toggle = document.querySelector('.c-header-menu-toggle');
const menu = document.getElementById('primary-menu');

if (toggle && menu) {
  const panels = menu.querySelectorAll('.c-primary-nav__panel');

  // Focus the new panel's first focusable element: when a panel is hidden the
  // browser drops focus to <body> (next tick), which the Tab trap below doesn't
  // track, so Tab would escape the overlay.
  const showPanel = (name) => {
    panels.forEach((panel) => {
      panel.hidden = panel.dataset.panel !== name;
    });
    menu
      .querySelector('.c-primary-nav__panel:not([hidden])')
      ?.querySelector('a[href], button:not([disabled])')
      ?.focus();
  };

  const setOpen = (open) => {
    toggle.setAttribute('aria-expanded', String(open));
    toggle.setAttribute('aria-label', open ? 'Close menu' : 'Menu');
    menu.hidden = !open;

    // Stops scroll reaching the page underneath and triggering the header's scroll-hide.
    document.documentElement.style.overflow = open ? 'hidden' : '';

    if (open) {
      showPanel('root');
      document.getElementById('mobile-search')?.setAttribute('hidden', '');
      document
        .querySelector('.c-header-search-toggle')
        ?.setAttribute('aria-expanded', 'false');
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

    if (event.target.closest('.c-primary-nav__link:not([data-open-panel])')) {
      setOpen(false); // real navigation — close rather than leave it open
    }
  });

  // Toggle sits outside #primary-menu, so it's added explicitly. Recomputed per keypress.
  const getTrapElements = () => {
    const panel = menu.querySelector('.c-primary-nav__panel:not([hidden])');
    const panelFocusables = panel
      ? [...panel.querySelectorAll('a[href], button:not([disabled])')]
      : [];
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

// Desktop mega-menu. Click/keyboard only — hover- or focus-driven open/close made
// dropdowns flicker while tabbing or moving the mouse through the bar.
const desktopNav = document.querySelector('.c-primary-nav-desktop');

if (desktopNav) {
  const triggers = [
    ...desktopNav.querySelectorAll(
      '.c-primary-nav-desktop__link[aria-controls], .c-primary-nav-desktop__more',
    ),
  ];

  const closeAll = (except) => {
    triggers.forEach((trigger) => {
      if (trigger === except) return;
      trigger.setAttribute('aria-expanded', 'false');
      const dropdown = document.getElementById(
        trigger.getAttribute('aria-controls'),
      );
      if (dropdown) dropdown.hidden = true;
    });
  };

  const openDropdown = (trigger) => {
    closeAll(trigger);
    trigger.setAttribute('aria-expanded', 'true');
    document.getElementById(trigger.getAttribute('aria-controls')).hidden =
      false;
  };

  triggers.forEach((trigger) => {
    trigger.addEventListener('click', () => {
      const isOpen = trigger.getAttribute('aria-expanded') === 'true';
      isOpen ? closeAll() : openDropdown(trigger);
    });
  });

  desktopNav.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    const openTrigger = triggers.find(
      (trigger) => trigger.getAttribute('aria-expanded') === 'true',
    );
    if (!openTrigger) return;
    closeAll();
    openTrigger.focus();
  });

  document.addEventListener('click', (event) => {
    if (!desktopNav.contains(event.target)) closeAll();
  });

  desktopNav.addEventListener('focusout', (event) => {
    if (event.relatedTarget && !desktopNav.contains(event.relatedTarget))
      closeAll();
  });

  // Overflow ("priority+") menu: top-level items that don't fit the bar
  // move into the burger's dropdown, trailing items first, so the client
  // can add menu items without breaking the bar. Fit is judged from the
  // last visible item's right edge against the list's, not scrollWidth —
  // the list is overflow:clip (no flash before this runs), which doesn't
  // report its overflow through scrollWidth.
  const navList = desktopNav.querySelector('.c-primary-nav-desktop__list');
  const navItems = [...desktopNav.querySelectorAll('[data-nav-item]')];
  const moreItem = desktopNav.querySelector(
    '.c-primary-nav-desktop__item--more',
  );
  const sections = [...desktopNav.querySelectorAll('[data-overflow-index]')];

  const layoutOverflow = () => {
    // Nothing to measure while the bar is display:none (below bp.$sm) or
    // swapped for the search form.
    if (!navList || !moreItem || navList.hidden || !navList.offsetWidth) return;

    navItems.forEach((item) => (item.hidden = false));
    moreItem.hidden = true;
    sections.forEach((section) => (section.hidden = true));

    const fits = () => {
      const last = [...navList.children].reverse().find((li) => !li.hidden);
      return (
        !last ||
        last.getBoundingClientRect().right <=
          navList.getBoundingClientRect().right + 0.5
      );
    };

    if (!fits()) {
      moreItem.hidden = false;
      for (let i = navItems.length - 1; i >= 0; i--) {
        navItems[i].hidden = true;
        sections[i].hidden = false;
        if (fits()) break;
      }
    }

    // Focus on an item that just moved into the burger would be dropped to
    // <body> as it's hidden — hand it to the burger instead (WCAG 2.4.3).
    const active = document.activeElement;
    if (
      active &&
      navItems.some((item) => item.hidden && item.contains(active))
    ) {
      moreItem.querySelector('.c-primary-nav-desktop__more').focus();
    }

    // An open dropdown whose trigger just moved/vanished would be orphaned.
    const stale = triggers.some(
      (trigger) =>
        trigger.getAttribute('aria-expanded') === 'true' &&
        trigger.closest('li').hidden,
    );
    if (stale) closeAll();
  };

  // Deferred to the next frame: layoutOverflow resizes the items it's
  // observing, and doing that inside the observer callback logs a
  // "ResizeObserver loop" error. Also collapses bursts into one pass.
  let layoutFrame;
  const scheduleLayout = () => {
    cancelAnimationFrame(layoutFrame);
    layoutFrame = requestAnimationFrame(layoutOverflow);
  };

  if (navList && moreItem && sections.length) {
    const observer = new ResizeObserver(scheduleLayout);
    // The container, for viewport changes. The list itself is skipped: its
    // size changes as items hide/show.
    observer.observe(navList.closest('.o-container'));
    // The items, for text-only zoom, a larger default font size or text
    // spacing, which widen labels without resizing the container
    // (WCAG 1.4.4/1.4.12). Hiding an item for overflow resizes it too, but
    // the next pass lands on the same state, so this settles.
    navItems.forEach((item) => observer.observe(item));
    document.fonts?.ready.then(scheduleLayout);
  }

  // Desktop search (Figma "Desktop | Search") replaces the L1 list within
  // the same bar rather than opening a separate overlay.
  const searchToggle = desktopNav.querySelector(
    '.c-primary-nav-desktop__search-toggle',
  );
  const searchForm = document.getElementById('desktop-search');

  if (searchToggle && searchForm && navList) {
    const setSearchOpen = (open) => {
      closeAll();
      searchToggle.setAttribute('aria-expanded', String(open));
      navList.hidden = open;
      searchForm.hidden = !open;
      if (open) searchForm.querySelector('input').focus();
      else layoutOverflow();
    };

    searchToggle.addEventListener('click', () => setSearchOpen(true));
    searchForm
      .querySelector('[data-search-close]')
      .addEventListener('click', () => {
        setSearchOpen(false);
        searchToggle.focus();
      });

    searchForm.addEventListener('keydown', (event) => {
      if (event.key !== 'Escape') return;
      setSearchOpen(false);
      searchToggle.focus();
    });
  }
}
