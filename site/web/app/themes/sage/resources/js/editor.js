import domReady from '@wordpress/dom-ready';
import './video-background.js';

// The editor canvas is an iframe with its own custom-element registry, so the
// <wa-*> components must be defined inside it, not just in this parent window.
const canvasSrc = window.sageEditorCanvas?.componentsSrc;
const hooked = new WeakSet();

const injectComponents = (iframe) => {
  const doc = iframe.contentDocument;
  if (!canvasSrc || !doc?.head || doc.head.querySelector('script[data-sage-canvas]')) return;

  const script = doc.createElement('script');
  script.type = 'module';
  script.src = canvasSrc;
  script.dataset.sageCanvas = '';
  doc.head.appendChild(script);
};

const checkCanvases = () => {
  document.querySelectorAll('iframe[name="editor-canvas"]').forEach((iframe) => {
    if (!hooked.has(iframe)) {
      hooked.add(iframe);
      iframe.addEventListener('load', () => injectComponents(iframe));
    }
    injectComponents(iframe);
  });
};

domReady(() => {
  let queued = false;

  new MutationObserver(() => {
    if (queued) return;
    queued = true;
    requestAnimationFrame(() => {
      queued = false;
      checkCanvases();
    });
  }).observe(document.body, { childList: true, subtree: true });

  checkCanvases();
});
