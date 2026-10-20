// Tiles are <button data-media-gallery-item> with data-* media info; a click fills the
// <wa-dialog> with an <img>/<video> instead of pre-rendering full-size media.
// wa-dialog handles focus trap and Esc — this only restores focus on close (WCAG 2.4.3).
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
        // No autoplay — unexpected sound on open is a startle hazard.
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
