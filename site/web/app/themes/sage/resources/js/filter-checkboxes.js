// Mobile disclosure for .c-filter-checkboxes: the toggle button opens/closes
// the grouped checkbox panel and always shows the current selected-filter
// count. Desktop shows the panel permanently via CSS regardless of the
// `hidden` attribute this toggles — see _filter-checkboxes.scss.
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
    const count = panel.querySelectorAll('input[type="checkbox"]:checked').length;
    label.textContent = count === 1 ? '1 filter selected' : `${count} filters selected`;
  };

  panel.addEventListener('change', (event) => {
    if (event.target.matches('input[type="checkbox"]')) updateLabel();
  });
});
