import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';
import App from './App';
import './event-timetable.scss';

domReady(() => {
  const root = document.getElementById('bbb-event-timetable');
  if (root) createRoot(root).render(<App />);
});
