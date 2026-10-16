import apiFetch from '@wordpress/api-fetch';

export const META = '_term_schedule';

// ISO weekday numbers, matching App\TermTimetable\Schedule.
export const WEEKDAYS = [
  { n: 1, label: 'Monday' },
  { n: 2, label: 'Tuesday' },
  { n: 3, label: 'Wednesday' },
  { n: 4, label: 'Thursday' },
  { n: 5, label: 'Friday' },
  { n: 6, label: 'Saturday' },
  { n: 7, label: 'Sunday' },
];

export const dayLabel = (n) => WEEKDAYS.find((d) => d.n === Number(n))?.label ?? '';

export const emptySchedule = (category = 0) => ({
  start: '',
  end: '',
  category,
  sessions: [],
  closures: [],
  cancellations: [],
  extras: [],
});

export const uid = () =>
  window.crypto?.randomUUID?.() ?? `id-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 8)}`;

const toMinutes = (hhmm) => {
  const [h, m] = String(hhmm).split(':').map(Number);
  return h * 60 + m;
};

const fromMinutes = (mins) => {
  const clamped = Math.max(0, Math.min(23 * 60 + 59, mins));
  return `${String(Math.floor(clamped / 60)).padStart(2, '0')}:${String(clamped % 60).padStart(2, '0')}`;
};

export const addMinutes = (hhmm, mins) => fromMinutes(toMinutes(hhmm) + mins);

export const DEFAULT_LENGTH = 45;

/** Where a new session on a day should start: after the day's last session, or 10:00. */
export const nextFreeStart = (sessions, weekday) => {
  const ends = sessions.filter((s) => s.weekday === weekday).map((s) => s.end).sort();
  return ends.length ? ends[ends.length - 1] : '10:00';
};

export const byStart = (a, b) => a.start.localeCompare(b.start) || a.end.localeCompare(b.end);

/** "2027-01-01" → "Fri 1 Jan 2027", for the summary line. */
export const friendlyDate = (ymd) =>
  ymd ? new Date(`${ymd}T12:00:00Z`).toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric', timeZone: 'UTC' }) : '';

export const weeksIn = (start, end) => {
  if (!start || !end || end < start) return 0;
  const days = (Date.parse(`${end}T00:00:00Z`) - Date.parse(`${start}T00:00:00Z`)) / 86400000 + 1;
  return Math.ceil(days / 7);
};

export const api = {
  load: (id) => apiFetch({ path: `/wp/v2/bbb_term/${id}?context=edit` }),
  save: (id, data) => apiFetch({ path: id ? `/wp/v2/bbb_term/${id}` : '/wp/v2/bbb_term', method: 'POST', data }),
  terms: () => apiFetch({ path: '/wp/v2/bbb_term?context=edit&per_page=100&status=publish,draft&_fields=id,title,meta' }),
  categories: () => apiFetch({ path: '/bbb/v1/term-categories' }),
  classes: (category) => apiFetch({ path: `/bbb/v1/term-classes?category=${category || 0}` }),
  preview: (schedule, term) => apiFetch({ path: '/bbb/v1/term-preview', method: 'POST', data: { schedule, term } }),
};
