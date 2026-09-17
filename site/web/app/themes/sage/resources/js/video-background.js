// Pause/play toggle for decorative autoplaying background videos
// (resources/views/components/video.blade.php, variant="background").
// Required even for muted/looping decorative video per WCAG 2.2.2
// (Pause, Stop, Hide).
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
    if (reducedMotion.matches) video.pause();
  };

  respectReducedMotion();
  reducedMotion.addEventListener('change', respectReducedMotion);
});
