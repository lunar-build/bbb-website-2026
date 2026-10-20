document.querySelectorAll('[data-video-block]').forEach((wrapper) => {
  const video = wrapper.querySelector('[data-video-block-video]');
  const toggle = wrapper.querySelector('[data-video-block-toggle]');
  if (!video || !toggle) return;

  const pauseIcon = toggle.querySelector('[data-video-block-icon="pause"]');
  const playIcon = toggle.querySelector('[data-video-block-icon="play"]');
  const label = toggle.dataset.videoBlockLabel || 'video';

  const setPlayingState = (playing) => {
    pauseIcon.hidden = !playing;
    playIcon.hidden = playing;
    toggle.setAttribute('aria-label', `${playing ? 'Pause' : 'Play'} ${label}`);
  };

  // Native controls are the no-JS fallback only; they come back on first play for seek/volume/fullscreen.
  video.removeAttribute('controls');
  toggle.hidden = false;
  setPlayingState(!video.paused);

  video.addEventListener('play', () => {
    video.setAttribute('controls', '');
    setPlayingState(true);
  });
  video.addEventListener('pause', () => setPlayingState(false));

  toggle.addEventListener('click', () => {
    if (video.paused) {
      video.play();
    } else {
      video.pause();
    }
  });
});
