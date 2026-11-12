// TEMPORARY: ?links=inline / ?links=body switches the mid-text link look for comparison (remembered in localStorage).
const requested = new URLSearchParams(window.location.search).get('links');

try {
  if (requested) localStorage.setItem('links', requested);
  document.body.classList.toggle('links-inline', localStorage.getItem('links') === 'inline');
} catch {
  document.body.classList.toggle('links-inline', requested === 'inline');
}
