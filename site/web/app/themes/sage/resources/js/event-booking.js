// MEC's ticket counter only disables "−" (and only when clicked down to 0); it never
// disables "+" at the ticket limit. Keep both buttons in step with the input's min/max.
const COUNTER = '.mec-event-ticket-input-wrapper';

function syncCounter(input) {
  const wrapper = input.closest(COUNTER);
  if (!wrapper) return;

  const count = Number(input.value) || 0;
  const min = Number(input.min) || 0;
  const max = input.max === '' ? Infinity : Number(input.max);

  wrapper.querySelector('.minus')?.classList.toggle('dis', count <= min);
  wrapper.querySelector('.plus')?.classList.toggle('dis', count >= max);
}

function syncAll() {
  document.querySelectorAll(`${COUNTER} input.in-num`).forEach(syncCounter);
}

if (document.querySelector(COUNTER)) {
  syncAll();

  // MEC fires its changes through jQuery, which native listeners can't hear.
  window.jQuery?.(document).on('change', `${COUNTER} input.in-num`, (e) => syncCounter(e.target));
  document.addEventListener('input', (e) => e.target.matches?.(`${COUNTER} input.in-num`) && syncCounter(e.target));
}

// A verify/cancel link lands on the event page with its result well below the title;
// bring it into view and announce it, as gravity-forms.js does for a sent form.
const notice = document.querySelector('[data-booking-notice]');
if (notice) {
  notice.setAttribute('tabindex', '-1');
  notice.scrollIntoView({ block: 'center', behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
  notice.focus({ preventScroll: true });
}
