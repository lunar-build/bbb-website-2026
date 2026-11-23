// Pushes GA4 events to the GTM dataLayer (the container is output by app/filters.php).
// Links classify themselves: rules in analytics-rules.js, then mailto, documents and external domains.
// Override or add events on a single link with
// attributes: data-ga-event="scheme_link_click" data-ga-scheme="borrow_a_bike" data-ga-district="bristol".
import { emailLabel, fileDetails, findLinkRule, formName, partnerFor, slugify } from './analytics-rules.js';

window.dataLayer = window.dataLayer || [];

const DOCUMENT_EXTENSIONS = ['pdf', 'gpx', 'kml', 'zip'];

const track = (event, parameters) => window.dataLayer.push({ event, ...parameters });

// The link's visible words, for telling apart links that share a destination or domain.
const linkText = (link) => slugify(link.textContent.replace(/\(opens in a new tab\)/gi, '').trim()).slice(0, 80);

// data-ga-cta-label → cta_label
const attributeParameters = (link) =>
  Object.fromEntries(
    Object.entries(link.dataset)
      .filter(([name]) => name.startsWith('ga') && name !== 'gaEvent')
      .map(([name, value]) => [
        name.slice(2).replace(/^[A-Z]/, (letter) => letter.toLowerCase()).replace(/[A-Z]/g, (letter) => `_${letter.toLowerCase()}`),
        value,
      ]),
  );

document.addEventListener('click', (clickEvent) => {
  // Web Awesome buttons with an href render their <a> in a shadow root, so the click retargets to the host.
  const link = clickEvent.target.closest('a[href], wa-button[href]');

  if (!link) {
    return;
  }

  if (link.dataset.gaEvent) {
    track(link.dataset.gaEvent, attributeParameters(link));

    return;
  }

  // A session in an Events Timetable.
  if (link.matches('.c-events-timetable__link')) {
    const category = link.querySelector('.c-events-timetable__category')?.textContent;

    track('timetable_session_click', {
      session_name: slugify(link.querySelector('.c-events-timetable__name')?.textContent ?? ''),
      session_day: (link.closest('[data-day]')?.dataset.day ?? '').toLowerCase(),
      ...(category ? { session_category: slugify(category) } : {}),
    });

    return;
  }

  const url = new URL(link.getAttribute('href'), location.href);

  if (url.protocol === 'tel:') {
    track('contact_click', { contact_method: 'phone', contact_label: linkText(link) });

    return;
  }

  if (url.protocol === 'mailto:') {
    track('contact_click', {
      contact_method: 'email',
      contact_label: emailLabel(decodeURIComponent(url.pathname)),
    });

    return;
  }

  const rule = findLinkRule(url);

  if (rule) {
    const cardTitle = link.closest('.c-link-card__card')?.querySelector('.c-link-card__title')?.textContent;
    const cardDistrict = cardTitle ? slugify(cardTitle).replace(/_council$/, '') : null;
    const district = rule.districtFromCard && cardDistrict ? { district: rule.districtAliases?.[cardDistrict] ?? cardDistrict } : {};

    const text = rule.event === 'cta_click' ? { link_text: linkText(link) } : {};

    track(rule.event, { ...rule.parameters, ...district, ...text });

    return;
  }

  // A business's own website from a shop card or a map pin's popup.
  const shop = link.closest('.c-business-card__card, .c-map-listing__info');
  const shopName = shop?.querySelector('.c-business-card__name, .c-map-listing__info-name')?.textContent;

  if (shopName && url.hostname !== location.hostname && url.protocol.startsWith('http')) {
    const directions = /(^|\.)maps\.|\/maps(\/|\?|$)/.test(url.hostname + url.pathname);

    track('shop_click', {
      shop_name: slugify(shopName),
      link_type: directions ? 'directions' : 'website',
      click_source: link.closest('.c-map-listing__info') ? 'map_popup' : 'card',
    });

    return;
  }

  const extension = url.pathname.split('.').pop().toLowerCase();

  if (DOCUMENT_EXTENSIONS.includes(extension)) {
    const fileName = decodeURIComponent(url.pathname.split('/').pop().replace(/\.[^.]+$/, ''));

    track('resource_download', { ...fileDetails(fileName.toLowerCase()), file_extension: extension });

    return;
  }

  if (url.hostname && url.hostname !== location.hostname) {
    // A route on the cycle planner: /leisure-rides/<route name>.
    const route = url.hostname.startsWith('cycleplanner.') ? url.pathname.match(/^\/leisure-rides\/([^/]+)/)?.[1] : null;

    track('outbound_click', {
      link_domain: url.hostname,
      partner: partnerFor(url),
      link_url: url.origin + url.pathname,
      link_text: linkText(link),
      ...(route ? { route_name: slugify(decodeURIComponent(route)) } : {}),
    });
  }
});

// A filter ticked on the Map Listing and archive pages (archive-filters.js does the swap). Name is
// filter_{taxonomy}[], value the term slug; unticking isn't counted.
document.addEventListener('change', (changeEvent) => {
  const checkbox = changeEvent.target;

  if (!checkbox.matches?.('wa-checkbox') || !checkbox.closest('[data-archive-filters]') || !checkbox.checked) {
    return;
  }

  track('filter_use', { filter_name: checkbox.getAttribute('name')?.replace(/^filter_|\[\]$/g, ''), filter_value: checkbox.getAttribute('value') });
});

// Video block (<video data-video-block-video>): media events don't bubble, so listen in the capture phase.
// Start once per video, then 25/50/75% watched and complete. Seeking past a milestone counts it too.
const VIDEO_MILESTONES = [25, 50, 75];
const videosStarted = new WeakSet();
const videoMilestonesSent = new WeakMap();
const videoTitle = (video) => slugify(video.getAttribute('aria-label') ?? '');

document.addEventListener(
  'play',
  ({ target: video }) => {
    if (!video.matches?.('[data-video-block-video]') || videosStarted.has(video)) {
      return;
    }

    videosStarted.add(video);
    track('video_start', { video_title: videoTitle(video) });
  },
  true,
);

document.addEventListener(
  'timeupdate',
  ({ target: video }) => {
    if (!video.matches?.('[data-video-block-video]') || !video.duration) {
      return;
    }

    const sent = videoMilestonesSent.get(video) ?? new Set();
    const percent = (video.currentTime / video.duration) * 100;

    VIDEO_MILESTONES.filter((milestone) => percent >= milestone && !sent.has(milestone)).forEach((milestone) => {
      sent.add(milestone);
      track('video_progress', { video_title: videoTitle(video), video_percent: milestone });
    });

    videoMilestonesSent.set(video, sent);
  },
  true,
);

document.addEventListener(
  'ended',
  ({ target: video }) => {
    if (video.matches?.('[data-video-block-video]')) {
      track('video_complete', { video_title: videoTitle(video) });
    }
  },
  true,
);

// Gravity Forms confirmation (AJAX submit); a page-reload confirmation is not covered.
window.jQuery?.(document).on('gform_confirmation_loaded', (loadedEvent, formId) => {
  track('form_submission', { form_name: formName(window.analyticsFormNames?.[formId], formId) });
});

// Newsletter embed (Theme Options > Footer, or a Form block). Counts a submit attempt, as the vendor's
// form confirms in its own iframe or script, which the page can't see.
document.addEventListener('submit', (submitEvent) => {
  if (!submitEvent.target.closest('.c-form__embed')) {
    return;
  }

  track('newsletter_signup', { form_location: submitEvent.target.closest('.c-form--flush-bottom') ? 'footer' : 'page' });
});
