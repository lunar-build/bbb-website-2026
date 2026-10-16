import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';
import App from './App';
import './term-timetable.scss';

domReady(() => {
  const root = document.getElementById('bbb-term-timetable');
  if (root) createRoot(root).render(<App />);
});
