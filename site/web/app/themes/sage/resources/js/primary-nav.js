// Mobile nav overlay: open/close via .c-header-menu-toggle, plus panel
// drill-in/back between the root L1 list and each L1 item's level-2 panel.
const toggle = document.querySelector('.c-header-menu-toggle');
const menu = document.getElementById('primary-menu');
const siteHeader = document.querySelector('lunar-site-header');

if (toggle && menu) {
  const panels = menu.querySelectorAll('.c-primary-nav__panel');

  // Overlay top padding depends on the header's real rendered height, not a
  // fixed guess — measured fresh on each open.
  const syncHeaderHeight = () => {
    if (!siteHeader) return;
    document.documentElement.style.setProperty('--header-height', `${siteHeader.getBoundingClientRect().height}px`);
  };

  // Also moves focus into the new panel — a focused element loses focus to
  // <body> once its panel is hidden, which would let Tab escape the trap.
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

    // Without this, scrolling past the overlay's content reaches the page
    // underneath and triggers the header's own scroll-hide behaviour.
    document.documentElement.style.overflow = open ? 'hidden' : '';

    if (open) {
      syncHeaderHeight();
      showPanel('root');
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

    if (event.target.closest('.c-primary-nav__link:not([data-open-panel])')) {
      setOpen(false); // real navigation — close rather than leave it open
    }
  });

  // Toggle sits outside #primary-menu, so it's included explicitly as the
  // trap's start/end. Recomputed each keypress since the visible panel changes.
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

// Desktop dropdown mega-menu. Hover-to-open (short close delay so moving
// diagonally into the dropdown doesn't close it early), plus click and
// keyboard support.
const desktopNav = document.querySelector('.c-primary-nav-desktop');

if (desktopNav) {
  const triggers = [...desktopNav.querySelectorAll('.c-primary-nav-desktop__link[aria-controls]')];
  let closeTimer = null;

  const closeAll = (except) => {
    triggers.forEach((trigger) => {
      if (trigger === except) return;
      trigger.setAttribute('aria-expanded', 'false');
      const dropdown = document.getElementById(trigger.getAttribute('aria-controls'));
      if (dropdown) dropdown.hidden = true;
    });
  };

  const openDropdown = (trigger) => {
    clearTimeout(closeTimer);
    closeAll(trigger);
    trigger.setAttribute('aria-expanded', 'true');
    document.getElementById(trigger.getAttribute('aria-controls')).hidden = false;
  };

  const scheduleClose = () => {
    clearTimeout(closeTimer);
    closeTimer = setTimeout(() => closeAll(), 150);
  };

  triggers.forEach((trigger) => {
    const dropdown = document.getElementById(trigger.getAttribute('aria-controls'));
    const item = trigger.closest('.c-primary-nav-desktop__item');

    trigger.addEventListener('click', () => {
      const isOpen = trigger.getAttribute('aria-expanded') === 'true';
      isOpen ? closeAll() : openDropdown(trigger);
    });

    // Tab alone must NOT open the dropdown — only hover or an explicit
    // Enter/Space (native <button> activation, handled by the click
    // listener above) should. Focusing a trigger still closes any OTHER
    // trigger's dropdown though (leaving this one's own alone, in case
    // focus is arriving back on the trigger that owns it), so tabbing
    // past a previously Enter-opened dropdown closes it behind you.
    trigger.addEventListener('focus', () => closeAll(trigger));

    item?.addEventListener('mouseenter', () => openDropdown(trigger));
    item?.addEventListener('mouseleave', scheduleClose);
    dropdown?.addEventListener('mouseenter', () => clearTimeout(closeTimer));
    dropdown?.addEventListener('mouseleave', scheduleClose);
  });

  // Leaf L1 items have no dropdown but still sit in tab order between
  // triggers — focusing one should close whatever was open.
  const leaves = [...desktopNav.querySelectorAll('.c-primary-nav-desktop__list > .c-primary-nav-desktop__item > a.c-primary-nav-desktop__link')];
  leaves.forEach((leaf) => {
    leaf.addEventListener('focus', () => closeAll());
  });

  desktopNav.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    const openTrigger = triggers.find((trigger) => trigger.getAttribute('aria-expanded') === 'true');
    if (!openTrigger) return;
    closeAll();
    openTrigger.focus();
  });

  document.addEventListener('click', (event) => {
    if (!desktopNav.contains(event.target)) closeAll();
  });

  desktopNav.addEventListener('focusout', (event) => {
    if (event.relatedTarget && !desktopNav.contains(event.relatedTarget)) closeAll();
  });

  // Desktop search (Figma "Desktop | Search") replaces the L1 list within
  // the same bar rather than opening a separate overlay.
  const searchToggle = desktopNav.querySelector('.c-primary-nav-desktop__search-toggle');
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
    searchForm.querySelector('[data-search-close]').addEventListener('click', () => {
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
