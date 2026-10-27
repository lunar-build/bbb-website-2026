import domReady from '@wordpress/dom-ready';
import { dispatch, select, subscribe } from '@wordpress/data';
import './video-background.js';

// New Heading blocks start in Blue medium; editors can pick another colour.
// Done on insertion rather than as an attribute default: a default would apply to every
// saved heading that has no colour, and their stored markup would then fail validation
// ("Block contains unexpected or invalid content").
const knownBlocks = new Set();
subscribe(() => {
  const editor = select('core/block-editor');
  if (!editor?.getClientIdsWithDescendants) return;

  editor.getClientIdsWithDescendants().forEach((clientId) => {
    if (knownBlocks.has(clientId)) return;
    knownBlocks.add(clientId);

    const block = editor.getBlock(clientId);
    const { content, textColor, style } = block?.attributes ?? {};
    if (block?.name !== 'core/heading' || content || textColor || style?.color) return;

    dispatch('core/block-editor').__unstableMarkNextChangeAsNotPersistent?.();
    dispatch('core/block-editor').updateBlockAttributes(clientId, { textColor: 'blue-medium' });
  });
});

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
