// Mobile disclosure for .c-filter-checkboxes (toggle + selected-count). Desktop shows
// the panel via CSS regardless of the `hidden` attribute — see _filter-checkboxes.scss.
document.querySelectorAll('.c-filter-checkboxes').forEach((root) => {
  const toggle = root.querySelector('.c-filter-checkboxes__toggle');
  const panel = root.querySelector('.c-filter-checkboxes__panel');
  const label = root.querySelector('.c-filter-checkboxes__toggle-label');

  if (!toggle || !panel) return;

  toggle.addEventListener('click', () => {
    const open = toggle.getAttribute('aria-expanded') !== 'true';
    toggle.setAttribute('aria-expanded', String(open));
    panel.hidden = !open;
  });

  const updateLabel = () => {
    const count = [...panel.querySelectorAll('wa-checkbox')].filter(
      (checkbox) => checkbox.checked,
    ).length;
    label.textContent = count === 1 ? '1 filter selected' : `${count} filters selected`;
  };

  // Shadow DOM retargets `change` to the <wa-checkbox> host, so `.matches()` not `.closest()`.
  panel.addEventListener('change', (event) => {
    if (event.target.matches('wa-checkbox')) updateLabel();
  });
});
