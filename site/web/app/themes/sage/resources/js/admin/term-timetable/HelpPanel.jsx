import { TabPanel } from '@wordpress/components';
import { useState } from '@wordpress/element';

const STORAGE_KEY = 'bbbTermTimetableHelpOpen';

// Remembered per browser; storage can be blocked (private windows), so it's optional.
const remembered = () => {
  try {
    return window.localStorage.getItem(STORAGE_KEY);
  } catch {
    return null;
  }
};

const TABS = [
  { name: 'start', title: 'Getting started' },
  { name: 'move', title: 'Move & copy' },
  { name: 'publish', title: 'Exceptions & publishing' },
  { name: 'messages', title: 'Messages' },
  { name: 'know', title: 'Good to know' },
];

// One short topic at a time keeps the guide a compact band above the editor.
const PANELS = {
  start: (
    <div className="bbb-tt-help__panel">
      <section>
        <h2>1. Set up the term</h2>
        <ol>
          <li>
            Give it a <strong>name</strong> (e.g. “Spring term 2027”) and its <strong>first</strong> and <strong>last day</strong>.
          </li>
          <li>
            Leave <strong>Classes from</strong> on Bristol Cycling Centre unless the term is for other events.
          </li>
          <li>
            <strong>Repeating a previous term?</strong> In the Term timetables list, hover over it and click <strong>Duplicate</strong>: you get a draft copy with the same week, ready for new dates. Or, on a new term, pick it under <strong>Start from an existing term</strong>.
          </li>
        </ol>
      </section>
      <section>
        <h2>2. Build the week</h2>
        <p>You set up <strong>one week</strong>; it repeats every week of the term.</p>
        <ul>
          <li>
            <strong>Drag a class</strong> from the list on the left onto a day, or press its <strong>Add</strong> link or a day’s <strong>+ Add session</strong>.
          </li>
          <li>Set the start and end time, then <strong>Add session</strong>. New sessions start after that day’s last one.</li>
          <li>
            <strong>Click a session</strong> to change its class, day or times, or to <strong>Duplicate</strong> or <strong>Delete</strong> it.
          </li>
        </ul>
      </section>
    </div>
  ),
  move: (
    <div className="bbb-tt-help__panel">
      <section>
        <h2>3. Move and copy</h2>
        <ul>
          <li>
            <strong>Drag a session</strong> by its ⠿ handle onto another day to move it; it keeps its times.
          </li>
          <li>
            Hold <kbd>Alt</kbd> (<kbd>Option</kbd> on a Mac) while dropping to <strong>copy</strong> it instead.
          </li>
          <li>
            Keyboard: focus the ⠿ handle, press <kbd>Space</kbd>, use the arrow keys to reach a day, then <kbd>Space</kbd> to drop (<kbd>Esc</kbd> cancels).
          </li>
        </ul>
      </section>
    </div>
  ),
  publish: (
    <div className="bbb-tt-help__panel">
      <section>
        <h2>4. Exceptions</h2>
        <ul>
          <li>
            <strong>Closures</strong>: dates the centre is shut (bank holidays, breaks). Nothing runs.
          </li>
          <li>
            <strong>Cancelled sessions</strong>: one session off on one date; the rest of the week runs.
          </li>
          <li>
            <strong>Extra sessions</strong>: one-offs on top of the pattern, even on a closed day.
          </li>
        </ul>
      </section>
      <section>
        <h2>5. Publish</h2>
        <ul>
          <li>
            <strong>Save draft</strong> keeps your work but adds nothing to the calendar.
          </li>
          <li>
            <strong>Publish</strong> (or <strong>Update</strong>) writes every session into the events calendar; What’s on updates straight away. The summary line says how many.
          </li>
          <li>
            <strong>Switch to draft</strong> (or trashing the term) takes its sessions off the calendar again.
          </li>
        </ul>
      </section>
    </div>
  ),
  messages: (
    <div className="bbb-tt-help__panel">
      <section>
        <h2>Messages</h2>
        <p>
          <span className="bbb-tt-help__tag is-error">Red</span> must be fixed before you can publish:
        </p>
        <ul>
          <li>no class chosen, or a missing start/end time</li>
          <li>a session (or closure) that ends before it starts</li>
          <li>the same class booked twice at overlapping times on one day</li>
          <li>term dates missing, or the last day before the first</li>
        </ul>
        <p>
          <span className="bbb-tt-help__tag is-warning">Amber</span> is a heads-up; you can still publish:
        </p>
        <ul>
          <li>a session before 6am or after 11pm</li>
          <li>a closure outside the term, or a cancellation on the wrong day</li>
          <li>a class that’s no longer published (it’s skipped)</li>
          <li>a class with its own repeat rule in the events calendar (this term won’t change it)</li>
          <li>dates overlapping another published term (both run)</li>
        </ul>
      </section>
    </div>
  ),
  know: (
    <div className="bbb-tt-help__panel">
      <section>
        <h2>Good to know</h2>
        <ul>
          <li>
            <strong>Different classes can run at the same time</strong>, and back-to-back sessions (10:00–10:45, 10:45–11:30) are fine.
          </li>
          <li>
            Weeks that differ (half term, an alternate-week class) need their <strong>own term</strong>, or use extras and cancellations.
          </li>
          <li>Sessions can’t run past midnight.</li>
          <li>Sessions added by hand on a class in the events calendar are kept when you update a term. Change a class’s <em>weekly</em> times here, not on the class’s own page; if that page was already open when you updated a term, reload it before saving.</li>
          <li>A class set to repeat by itself in the events calendar (daily, weekly…) isn’t changed here.</li>
          <li>What’s on lists every current and upcoming published term automatically.</li>
        </ul>
      </section>
    </div>
  ),
};

/**
 * How to use the editor, folded into one line once you know it. Open by
 * default on a new, empty term; afterwards it remembers the last choice.
 * The messages listed here mirror App\TermTimetable\Schedule::validate() and
 * RestController::preview(); keep them in step.
 */
export default function HelpPanel({ isNew }) {
  const [open, setOpen] = useState(() => {
    const saved = remembered();
    return saved === null ? isNew : saved === '1';
  });

  const toggle = (e) => {
    setOpen(e.currentTarget.open);
    try {
      window.localStorage.setItem(STORAGE_KEY, e.currentTarget.open ? '1' : '0');
    } catch {
      // Not remembered; the panel still works.
    }
  };

  return (
    <details className="bbb-tt-help" open={open} onToggle={toggle}>
      <summary>
        <span className="bbb-tt-help__icon" aria-hidden="true">
          ?
        </span>
        How to use this screen
        <span className="bbb-tt-help__hint">{open ? 'Hide' : 'Show the quick guide'}</span>
      </summary>

      <TabPanel className="bbb-tt-help__tabs" tabs={TABS}>
        {(tab) => PANELS[tab.name]}
      </TabPanel>
    </details>
  );
}
