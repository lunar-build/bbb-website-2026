// Mobile nav overlay: open/close via .c-header-menu-toggle, plus panel
// drill-in/back between the root L1 list and each L1 item's level-2 panel.
const toggle = document.querySelector('.c-header-menu-toggle');
const menu = document.getElementById('primary-menu');

if (toggle && menu) {
  const panels = menu.querySelectorAll('.c-primary-nav__panel');

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
    menu
      .querySelector('.c-primary-nav__panel:not([hidden])')
      ?.querySelector('a[href], button:not([disabled])')
      ?.focus();
  };

  const setOpen = (open) => {
    toggle.setAttribute('aria-expanded', String(open));
    toggle.setAttribute('aria-label', open ? 'Close menu' : 'Menu');
    menu.hidden = !open;

    // Without this, scrolling past the overlay's content reaches the page
    // underneath and triggers the header's own scroll-hide behaviour.
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

  // Toggle sits outside #primary-menu, so it's included explicitly as the
  // trap's start/end. Recomputed each keypress since the visible panel changes.
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

// Desktop dropdown mega-menu. Click/tap and keyboard (Enter/Space, native
// <button> activation) only — no hover-to-open and no focus-driven
// open/close, since either caused dropdowns to flicker open/closed while
// just tabbing or moving the mouse through the bar.
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
  const moreItem = desktopNav.querySelector('.c-primary-nav-desktop__item--more');
  const sections = [
    ...desktopNav.querySelectorAll('[data-overflow-index]'),
  ];

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

    // An open dropdown whose trigger just moved/vanished would be orphaned.
    const stale = triggers.some(
      (trigger) =>
        trigger.getAttribute('aria-expanded') === 'true' &&
        trigger.closest('li').hidden,
    );
    if (stale) closeAll();
  };

  // Observe the container, not the list: the list's own size changes as
  // items hide/show, which would feed back into this observer.
  if (navList && moreItem && sections.length) {
    new ResizeObserver(layoutOverflow).observe(
      navList.closest('.o-container'),
    );
    document.fonts?.ready.then(layoutOverflow);
  }

  // Desktop search (Figma "Desktop | Search") replaces the L1 list within
  // the same bar rather than opening a separate overlay.
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
      if (!open) layoutOverflow();
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
