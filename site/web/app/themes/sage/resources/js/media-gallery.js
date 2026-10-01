// Every tile is a plain <button> (data-media-gallery-item) describing its
// media via data-* attributes; clicking it fills the block's <wa-dialog>
// with a real <img> or <video controls> rather than pre-rendering every
// tile's full-size media up front. wa-dialog traps focus and handles Esc
// itself — this only restores focus to the trigger on close (WCAG 2.4.3).
document.querySelectorAll('[data-media-gallery]').forEach((wrapper) => {
  const dialog = wrapper.querySelector('[data-media-gallery-dialog]');
  const body = wrapper.querySelector('[data-media-gallery-dialog-body]');
  if (!dialog || !body) return;

  let trigger = null;

  wrapper.querySelectorAll('[data-media-gallery-item]').forEach((button) => {
    button.addEventListener('click', () => {
      const { type, src, poster } = button.dataset;
      const label = button.getAttribute('aria-label') || '';

      body.innerHTML = '';

      if (type === 'video') {
        // No autoplay — an unexpected sound burst the instant the dialog
        // opens is a startle/surprise hazard; the visitor presses the
        // native controls' own play button instead.
        const video = document.createElement('video');
        video.src = src;
        video.controls = true;
        video.playsInline = true;
        if (poster) video.poster = poster;
        body.appendChild(video);
      } else {
        const img = document.createElement('img');
        img.src = src;
        img.alt = label;
        body.appendChild(img);
      }

      dialog.label = label;
      trigger = button;
      dialog.open = true;
    });
  });

  dialog.addEventListener('wa-after-hide', () => {
    body.innerHTML = '';

    if (trigger) {
      trigger.focus();
      trigger = null;
    }
  });
});
