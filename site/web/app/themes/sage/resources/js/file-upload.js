// Gravity Forms single file upload (see the gform_field_content filter in app/filters.php):
// once a file is picked, swap the "Choose a file" CTA for a chip with its name, size and a
// remove button. Delegated, since GF re-renders AJAX forms after a failed submit.
import paperclip from '../svg/paperclip.svg?raw';
import close from '../svg/close-light.svg?raw';

const formatSize = (bytes) =>
  bytes < 1024 * 1024 ? `${Math.max(1, Math.round(bytes / 1024))}KB` : `${(bytes / 1024 / 1024).toFixed(1)}MB`;

// While a file is shown the CTA is hidden, so the input (whose focus ring it draws) leaves the tab order.
const clear = (container) => {
  container.querySelector('.c-file-upload__file')?.remove();
  container.classList.remove('has-file');
  container.querySelector('input[type="file"]')?.removeAttribute('tabindex');
};

const show = (container, input, file) => {
  clear(container);

  const chip = document.createElement('div');
  chip.className = 'c-file-upload__file';
  chip.innerHTML = `
    <span class="c-file-upload__icon" aria-hidden="true">${paperclip}</span>
    <span class="c-file-upload__name"></span>
    <span class="c-file-upload__size"></span>
    <button type="button" class="c-file-upload__remove">${close}</button>
  `;
  chip.querySelector('.c-file-upload__name').textContent = file.name;
  chip.querySelector('.c-file-upload__size').textContent = formatSize(file.size);

  const remove = chip.querySelector('.c-file-upload__remove');
  remove.setAttribute('aria-label', `Remove ${file.name}`);
  remove.addEventListener('click', () => {
    input.value = '';
    input.dispatchEvent(new Event('change', { bubbles: true }));
    input.focus();
  });

  // Before GF's live validation message, so a "file too large" error still reads after it.
  const message = container.querySelector('.gfield_validation_message');
  container.insertBefore(chip, message);
  container.classList.add('has-file');
  input.setAttribute('tabindex', '-1');
};

document.addEventListener('change', (event) => {
  const input = event.target;
  const container = input.closest?.('.c-file-upload');
  if (!container || input.type !== 'file') return;

  const file = input.files?.[0];
  file ? show(container, input, file) : clear(container);
});
