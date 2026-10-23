// Forms submit over AJAX (blocks/form.blade.php). Gravity Forms scrolls to the result, but after a
// successful send focus stays on its hidden ajax iframe, so a screen reader would hear nothing.
// Errors are already handled: it focuses the validation summary.
const announce = (formId) => {
  const message = document.getElementById(`gform_confirmation_message_${formId}`);
  if (!message) return;

  message.setAttribute('tabindex', '-1');
  message.setAttribute('role', 'status');
  message.scrollIntoView({ block: 'center', behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
  message.focus({ preventScroll: true });
};

// Gravity Forms fires this jQuery event once the confirmation replaces the form.
window.jQuery?.(document).on('gform_confirmation_loaded', (_event, formId) => announce(formId));
