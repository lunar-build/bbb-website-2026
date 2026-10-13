// Archive filters (resources/views/archive.blade.php): ticking a checkbox or
// following a pagination link fetches that URL's full page and swaps in just
// its [data-archive-results] fragment, keeping real ?filter_{taxonomy}[]=
// URLs so every state is shareable and reloadable.
const form = document.querySelector('[data-archive-filters]');
const results = document.querySelector('[data-archive-results]');

if (results) {
  const root = results.closest('.c-archive');
  let controller;
  let pushed = false;

  const load = async (url, { focus = true } = {}) => {
    controller?.abort();
    controller = new AbortController();
    root.classList.add('is-loading');

    try {
      const response = await fetch(url, { signal: controller.signal });
      const html = await response.text();
      const next = new DOMParser()
        .parseFromString(html, 'text/html')
        .querySelector('[data-archive-results]');

      if (!response.ok || !next) throw new Error(`Archive fetch failed: ${response.status}`);

      results.innerHTML = next.innerHTML;
      results.dispatchEvent(new CustomEvent('archive:updated', { bubbles: true }));
      history.pushState({ archive: true }, '', url);
      pushed = true;

      if (focus) {
        const heading = results.querySelector('[data-archive-results-heading]');
        heading?.focus({ preventScroll: true });
        heading?.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    } catch (error) {
      if (error.name !== 'AbortError') window.location.href = url;
    } finally {
      root.classList.remove('is-loading');
    }
  };

  // Always from the unfiltered archive URL (form action), so a filter change
  // drops /page/N/ and leaves a term archive's own URL.
  const filterUrl = () => {
    const url = new URL(form.action);
    new FormData(form).forEach((value, name) => url.searchParams.append(name, value));
    return url.toString();
  };

  // wa-checkbox's `change` event retargets to the host element — see filter-checkboxes.js.
  form?.addEventListener('change', (event) => {
    if (event.target.matches('wa-checkbox')) load(filterUrl(), { focus: false });
  });

  form?.addEventListener('submit', (event) => {
    event.preventDefault();
    load(filterUrl(), { focus: false });
  });

  results.addEventListener('click', (event) => {
    const link = event.target.closest('.c-archive__pagination a');
    if (!link || event.metaKey || event.ctrlKey || event.shiftKey) return;

    event.preventDefault();
    load(link.href);
  });

  // Checkbox states belong to the page being left, so do a full reload on
  // back/forward rather than re-syncing every checkbox by hand. Only once
  // we've pushed — hash-only history (e.g. the skip link) also fires popstate.
  window.addEventListener('popstate', () => {
    if (pushed) window.location.reload();
  });
}
