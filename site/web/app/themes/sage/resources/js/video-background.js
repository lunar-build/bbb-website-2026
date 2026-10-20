// Pause/play toggle for background videos (components/video.blade.php) — required
// even for muted/looping video by WCAG 2.2.2.
document.querySelectorAll('[data-video-background]').forEach((wrapper) => {
  const video = wrapper.querySelector('video');
  const control = wrapper.querySelector('[data-video-control]');
  if (!video || !control) return;

  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

  const setPressed = (playing) => {
    control.setAttribute('aria-pressed', String(playing));
    control.setAttribute(
      'aria-label',
      playing ? control.dataset.labelPause : control.dataset.labelPlay,
    );
  };

  control.addEventListener('click', () => {
    if (video.paused) {
      video.play();
    } else {
      video.pause();
    }
  });

  video.addEventListener('play', () => setPressed(true));
  video.addEventListener('pause', () => setPressed(false));

  const respectReducedMotion = () => {
    if (reducedMotion.matches) {
      video.pause();
      setPressed(false);
    }
  };

  respectReducedMotion();
  reducedMotion.addEventListener('change', respectReducedMotion);
});
