// Video Hero's background video: autoplay/loop/muted, with a custom
// play/pause toggle required by WCAG 2.2.2 (Pause, Stop, Hide) whenever
// content autoplays. `_playing` state is driven by the video's own
// play/pause events rather than the button's own intent, so it stays
// correct whether playback started from autoplay, the toggle, or the
// browser pausing it (e.g. tab backgrounded).
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

document.querySelectorAll('[data-video-hero]').forEach((wrapper) => {
  const video = wrapper.querySelector('[data-video-hero-video]');
  const toggle = wrapper.querySelector('[data-video-hero-toggle]');
  if (!video || !toggle) return;

  const pauseIcon = toggle.querySelector('[data-video-hero-icon="pause"]');
  const playIcon = toggle.querySelector('[data-video-hero-icon="play"]');

  const setPlayingState = (playing) => {
    pauseIcon.hidden = !playing;
    playIcon.hidden = playing;
    toggle.setAttribute('aria-label', playing ? 'Pause background video' : 'Play background video');
  };

  const applyReducedMotion = () => {
    if (reducedMotion.matches) video.pause();
  };

  video.addEventListener('play', () => setPlayingState(true));
  video.addEventListener('pause', () => setPlayingState(false));

  // Unknown duration (metadata not loaded yet) defaults to "show the
  // control" — safer than briefly hiding a required pause affordance.
  video.addEventListener('loadedmetadata', () => {
    toggle.hidden = video.duration <= 5;
  });

  toggle.addEventListener('click', () => {
    if (video.paused) {
      video.play();
    } else {
      video.pause();
    }
  });

  reducedMotion.addEventListener('change', applyReducedMotion);
  applyReducedMotion();
  setPlayingState(!video.paused);
});
