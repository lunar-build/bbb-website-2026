// Video Hero background video with the play/pause toggle WCAG 2.2.2 requires for
// autoplay. State follows the video's own play/pause events, so it stays correct
// when the browser pauses it (e.g. tab backgrounded).
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

document.querySelectorAll('[data-video-hero]').forEach((wrapper) => {
  const video = wrapper.querySelector('[data-video-hero-video]');
  const toggle = wrapper.querySelector('[data-video-hero-toggle]');
  if (!video || !toggle) return;

  const pauseIcon = toggle.querySelector('[data-video-hero-icon="pause"]');
  const playIcon = toggle.querySelector('[data-video-hero-icon="play"]');
  const label = toggle.dataset.videoHeroLabel || 'background video';

  const setPlayingState = (playing) => {
    pauseIcon.hidden = !playing;
    playIcon.hidden = playing;
    toggle.setAttribute('aria-label', `${playing ? 'Pause' : 'Play'} ${label}`);
  };

  const applyReducedMotion = () => {
    if (reducedMotion.matches) video.pause();
  };

  video.addEventListener('play', () => setPlayingState(true));
  video.addEventListener('pause', () => setPlayingState(false));

  // Unknown duration defaults to showing the control.
  video.addEventListener('loadedmetadata', () => {
toggle.hidden = false;
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
