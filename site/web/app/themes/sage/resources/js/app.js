// Theme tokens only (--wa-color-* etc, used by base/_wa-theme.scss and the
// wa-* Shadow DOM components' internal styles) — not the full
// webawesome.css, which also pulls in native.css: a global, opinionated
// reset for plain HTML forms/tables/buttons/etc that fights our own design
// system for exactly those elements (input[type=radio] etc). Shadow DOM
// components (<wa-button>, <wa-card>) ship their own scoped styles via
// their JS modules (see components.js) independent of this either way.
import '@awesome.me/webawesome/dist/styles/themes/default.css';
import './components.js';
import './header-search.js';
import './primary-nav.js';
import '../styles/app.scss';

