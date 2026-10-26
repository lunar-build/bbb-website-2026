// Feedback widget for dev/staging only; PHP sets window.markerProject (see app/filters.php).
// Dynamic import keeps the SDK out of the bundle's critical path and unused on production.
const project = window.markerProject;

if (project) {
  import('@marker.io/browser').then(({ default: markerSDK }) => {
    markerSDK.loadWidget({ project });
  });
}
