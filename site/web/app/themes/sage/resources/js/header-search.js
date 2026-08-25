// Mobile-only search band toggle (placeholder ahead of the real search
// component landing in lunar-ui-components — see the header rebuild plan's
// Phase 4). Plain show/hide, no query handling beyond the form's own submit.
document.querySelectorAll('.c-header-search-toggle').forEach((toggle) => {
  const panel = document.getElementById(toggle.getAttribute('aria-controls'));
  if (!panel) return;

  const setOpen = (open) => {
    toggle.setAttribute('aria-expanded', String(open));
    toggle.setAttribute('aria-label', open ? 'Close search' : 'Search');
    panel.hidden = !open;
    if (open) panel.querySelector('input')?.focus();
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
