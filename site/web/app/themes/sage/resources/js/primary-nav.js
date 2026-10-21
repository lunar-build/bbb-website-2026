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
      '.c-primary-nav-desktop__link[aria-controls]',
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

  // Desktop search replaces the L1 list within the same bar (Figma "Desktop | Search").
  const searchToggle = desktopNav.querySelector(
    '.c-primary-nav-desktop__search-toggle',
  );
  const searchForm = document.getElementById('desktop-search');
  const list = desktopNav.querySelector('.c-primary-nav-desktop__list');

  if (searchToggle && searchForm && list) {
    const setSearchOpen = (open) => {
      closeAll();
      searchToggle.setAttribute('aria-expanded', String(open));
      list.hidden = open;
      searchForm.hidden = !open;
      if (open) searchForm.querySelector('input').focus();
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
