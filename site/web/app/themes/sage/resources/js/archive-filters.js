// Archive filters: a checkbox or pagination click fetches the URL and swaps in its
// [data-archive-results] fragment, keeping real ?filter_{taxonomy}[]= URLs shareable.
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

  // Built from the form action so a filter change drops /page/N/.
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

  // Full reload on back/forward rather than re-syncing checkboxes. Only after we've
  // pushed — hash-only history (e.g. the skip link) also fires popstate.
  window.addEventListener('popstate', () => {
    if (pushed) window.location.reload();
  });
}
