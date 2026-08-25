// Mobile search band toggle — placeholder ahead of the real search component (Phase 4).
document.querySelectorAll('.c-header-search-toggle').forEach((toggle) => {
  const panel = document.getElementById(toggle.getAttribute('aria-controls'));
  if (!panel) return;

  const setOpen = (open) => {
    toggle.setAttribute('aria-expanded', String(open));
    toggle.setAttribute('aria-label', open ? 'Close search' : 'Search');
    panel.hidden = !open;

    if (open) {
      panel.querySelector('input')?.focus();
      // Only one full-screen mobile overlay makes sense open at a time.
      const menuToggle = document.querySelector('.c-header-menu-toggle');
      if (menuToggle?.getAttribute('aria-expanded') === 'true') {
        menuToggle.click();
      }
    }
  };

  toggle.addEventListener('click', () => {
    setOpen(toggle.getAttribute('aria-expanded') !== 'true');
  });

  panel.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    setOpen(false);
    toggle.focus();
  });
});
