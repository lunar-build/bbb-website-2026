import { Button, SelectControl, TextControl } from '@wordpress/components';
import { byStart, dayLabel, uid } from './utils';

/** Closures, single-date cancellations and one-off extra sessions, as simple rows. */
export default function ExceptionsPanel({ schedule, classes, classesById, problemsFor, onChange }) {
  const update = (list, id, patch) => onChange({ [list]: schedule[list].map((row) => (row.id === id ? { ...row, ...patch } : row)) });
  const remove = (list, id) => onChange({ [list]: schedule[list].filter((row) => row.id !== id) });
  const add = (list, row) => onChange({ [list]: [...schedule[list], { id: uid(), ...row }] });

  const sessionOptions = [...schedule.sessions]
    .sort((a, b) => a.weekday - b.weekday || byStart(a, b))
    .map((s) => ({ value: s.id, label: `${dayLabel(s.weekday)} ${s.start}–${s.end}, ${classesById[s.event]?.title ?? 'class'}` }));

  const Problems = ({ id }) =>
    problemsFor(id).map((p) => (
      <p key={p.message} className="bbb-tt-row__problem">
        {p.message}
      </p>
    ));

  return (
    <div className="bbb-tt-exceptions">
      <section aria-labelledby="bbb-tt-closures">
        <h2 id="bbb-tt-closures">Closures</h2>
        <p className="bbb-tt-hint">Dates the centre is shut (bank holidays, breaks). No sessions run on them.</p>
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

      <section aria-labelledby="bbb-tt-cancellations">
        <h2 id="bbb-tt-cancellations">Cancelled sessions</h2>
        <p className="bbb-tt-hint">One session off on one date; the rest of the week runs as normal.</p>
        {schedule.cancellations.map((row) => (
          <div key={row.id} className="bbb-tt-row" data-ref={row.id}>
            <SelectControl __nextHasNoMarginBottom label="Session" value={row.session} options={[{ value: '', label: 'Choose…' }, ...sessionOptions]} onChange={(session) => update('cancellations', row.id, { session })} />
            <TextControl __nextHasNoMarginBottom label="Date" type="date" value={row.date} min={schedule.start} max={schedule.end} onChange={(date) => update('cancellations', row.id, { date })} />
            <Button variant="tertiary" isDestructive onClick={() => remove('cancellations', row.id)}>
              Remove
            </Button>
            <Problems id={row.id} />
          </div>
        ))}
        <Button variant="secondary" disabled={!schedule.sessions.length} onClick={() => add('cancellations', { session: '', date: schedule.start })}>
          + Cancel a session
        </Button>
      </section>

      <section aria-labelledby="bbb-tt-extras">
        <h2 id="bbb-tt-extras">Extra sessions</h2>
        <p className="bbb-tt-hint">One-off sessions on top of the weekly pattern.</p>
        {schedule.extras.map((row) => (
          <div key={row.id} className="bbb-tt-row" data-ref={row.id}>
            <SelectControl __nextHasNoMarginBottom label="Class" value={row.event || ''} options={[{ value: '', label: 'Choose…' }, ...classes.map((c) => ({ value: c.id, label: c.title }))]} onChange={(event) => update('extras', row.id, { event: Number(event) })} />
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
