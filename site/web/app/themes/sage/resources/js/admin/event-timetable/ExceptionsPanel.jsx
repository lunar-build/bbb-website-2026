import { Button, SelectControl, TextControl } from '@wordpress/components';
import { byStart, dayLabel, friendlyDate, uid } from './utils';

/** Closures, single-date cancellations and one-off extra sessions, as simple rows. */
export default function ExceptionsPanel({ schedule, events, eventsById, problemsFor, onChange }) {
  const update = (list, id, patch) => onChange({ [list]: schedule[list].map((row) => (row.id === id ? { ...row, ...patch } : row)) });
  const remove = (list, id) => onChange({ [list]: schedule[list].filter((row) => row.id !== id) });
  const add = (list, row) => onChange({ [list]: [...schedule[list], { id: uid(), ...row }] });

  const sessionOptions = [...schedule.sessions]
    .sort((a, b) => a.weekday - b.weekday || byStart(a, b))
    .map((s) => ({ value: s.id, label: `${dayLabel(s.weekday)} ${s.start}–${s.end}, ${eventsById[s.event]?.title ?? 'event'}` }));

  const Problems = ({ id }) =>
    problemsFor(id).map((p) => (
      <p key={p.message} className="bbb-tt-row__problem">
        {p.message}
      </p>
    ));

  const jump = (ref) => {
    const el = document.querySelector(`[data-ref="${CSS.escape(ref)}"]`);
    if (!el) return;
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    (el.querySelector('button, input, select') ?? el).focus({ preventScroll: true });
    el.classList.add('is-flagged');
    setTimeout(() => el.classList.remove('is-flagged'), 2000);
  };
  const range = (from, to) => (from && to && from !== to ? `${friendlyDate(from)} – ${friendlyDate(to)}` : friendlyDate(from));
  const sessionName = (id) => {
    const session = schedule.sessions.find((x) => x.id === id);
    return session ? `${eventsById[session.event]?.title ?? 'Session'} ${session.start}` : 'a session';
  };

  // Everything already in place, in date order, as chips that jump to the row.
  const inPlace = [
    ...schedule.closures.map((r) => ({ id: r.id, type: 'closure', date: r.from, text: `Closed ${range(r.from, r.to)}${r.label ? `: ${r.label}` : ''}` })),
    ...schedule.cancellations.map((r) => ({ id: r.id, type: 'cancellation', date: r.date, text: `${sessionName(r.session)} cancelled ${friendlyDate(r.date)}${r.reason ? `: ${r.reason}` : ''}` })),
    ...schedule.extras.map((r) => ({ id: r.id, type: 'extra', date: r.date, text: `Extra: ${eventsById[r.event]?.title ?? 'session'} ${friendlyDate(r.date)} ${r.start}` })),
  ].sort((a, b) => String(a.date).localeCompare(String(b.date)));

  const Empty = ({ children }) => <p className="bbb-tt-empty">{children}</p>;
  const Count = ({ n }) => <span className="bbb-tt-count" aria-label={`${n} in place`}>{n}</span>;

  return (
    <div className="bbb-tt-exceptions">
      <div className="bbb-tt-inplace" aria-labelledby="bbb-tt-inplace-title">
        <h2 id="bbb-tt-inplace-title">Different on certain dates?</h2>
        <p className="bbb-tt-hint">
          Your weekly pattern repeats every week. For a date that’s different, use the three boxes below: <strong>close</strong> a day or run of days, <strong>cancel</strong> one session on one date, or <strong>add</strong> a one-off session. They show on the What’s on timetable.
        </p>
        {inPlace.length ? (
          <ul className="bbb-tt-inplace__list" aria-label="Already in place">
            {inPlace.map((c) => (
              <li key={c.id}>
                <button type="button" className={`bbb-tt-inplace__chip bbb-tt-inplace__chip--${c.type}`} onClick={() => jump(c.id)}>
                  {c.text}
                </button>
              </li>
            ))}
          </ul>
        ) : (
          <p className="bbb-tt-hint">Nothing added yet.</p>
        )}
      </div>

      <section className="bbb-tt-exc bbb-tt-exc--closure" aria-labelledby="bbb-tt-closures">
        <h2 id="bbb-tt-closures">
          Closures <Count n={schedule.closures.length} />
        </h2>
        <p className="bbb-tt-hint">Shut for a day or more (bank holiday, staff training, a break)? Add the dates and, if you like, a message. Nothing runs on those dates, and What’s on shows “Closed” with your message.</p>
        {!schedule.closures.length && <Empty>No closures yet.</Empty>}
        {schedule.closures.map((row) => (
          <div key={row.id} className="bbb-tt-row" data-ref={row.id}>
            <TextControl __nextHasNoMarginBottom label="From" type="date" value={row.from} onChange={(from) => update('closures', row.id, { from, to: row.to < from ? from : row.to })} />
            <TextControl __nextHasNoMarginBottom label="To" type="date" value={row.to} min={row.from} onChange={(to) => update('closures', row.id, { to })} />
            <TextControl __nextHasNoMarginBottom label="Reason (optional)" value={row.label} onChange={(label) => update('closures', row.id, { label })} />
            <Button variant="tertiary" isDestructive onClick={() => remove('closures', row.id)}>
              Remove
            </Button>
            <Problems id={row.id} />
          </div>
        ))}
        <Button variant="secondary" onClick={() => add('closures', { from: schedule.start, to: schedule.start, label: '' })}>
          + Add closure
        </Button>
      </section>

      <section className="bbb-tt-exc bbb-tt-exc--cancellation" aria-labelledby="bbb-tt-cancellations">
        <h2 id="bbb-tt-cancellations">
          Cancelled sessions <Count n={schedule.cancellations.length} />
        </h2>
        <p className="bbb-tt-hint">One session off on one date; the rest of the day runs as normal. It stays on What’s on, struck through and marked “Cancelled” with your reason. You do this here, not in the events calendar.</p>
        {!schedule.cancellations.length && <Empty>No cancelled sessions yet. Add the session that’s off, then pick the date.</Empty>}
        {schedule.cancellations.map((row) => (
          <div key={row.id} className="bbb-tt-row" data-ref={row.id}>
            <SelectControl __nextHasNoMarginBottom label="Session" value={row.session} options={[{ value: '', label: 'Choose…' }, ...sessionOptions]} onChange={(session) => update('cancellations', row.id, { session })} />
            <TextControl __nextHasNoMarginBottom label="Date" type="date" value={row.date} min={schedule.start} max={schedule.end} onChange={(date) => update('cancellations', row.id, { date })} />
            <TextControl __nextHasNoMarginBottom label="Reason (optional)" placeholder="e.g. Instructor ill" value={row.reason ?? ''} onChange={(reason) => update('cancellations', row.id, { reason })} />
            <Button variant="tertiary" isDestructive onClick={() => remove('cancellations', row.id)}>
              Remove
            </Button>
            <Problems id={row.id} />
          </div>
        ))}
        <Button variant="secondary" disabled={!schedule.sessions.length} onClick={() => add('cancellations', { session: '', date: schedule.start, reason: '' })}>
          + Cancel a session
        </Button>
      </section>

      <section className="bbb-tt-exc bbb-tt-exc--extra" aria-labelledby="bbb-tt-extras">
        <h2 id="bbb-tt-extras">
          Extra sessions <Count n={schedule.extras.length} />
        </h2>
        <p className="bbb-tt-hint">A one-off session on top of the weekly pattern, for example an extra Saturday class. It shows on What’s on with an “Extra session” label.</p>
        {!schedule.extras.length && <Empty>No extra sessions yet.</Empty>}
        {schedule.extras.map((row) => (
          <div key={row.id} className="bbb-tt-row" data-ref={row.id}>
            <SelectControl __nextHasNoMarginBottom label="Event" value={row.event || ''} options={[{ value: '', label: 'Choose…' }, ...events.map((c) => ({ value: c.id, label: c.title }))]} onChange={(event) => update('extras', row.id, { event: Number(event) })} />
            <TextControl __nextHasNoMarginBottom label="Date" type="date" value={row.date} onChange={(date) => update('extras', row.id, { date })} />
            <TextControl __nextHasNoMarginBottom label="Starts" type="time" step={300} value={row.start} onChange={(start) => update('extras', row.id, { start })} />
            <TextControl __nextHasNoMarginBottom label="Ends" type="time" step={300} value={row.end} onChange={(end) => update('extras', row.id, { end })} />
            <Button variant="tertiary" isDestructive onClick={() => remove('extras', row.id)}>
              Remove
            </Button>
            <Problems id={row.id} />
          </div>
        ))}
        <Button variant="secondary" onClick={() => add('extras', { event: 0, date: schedule.start, start: '10:00', end: '10:45' })}>
          + Add extra session
        </Button>
      </section>
    </div>
  );
}
