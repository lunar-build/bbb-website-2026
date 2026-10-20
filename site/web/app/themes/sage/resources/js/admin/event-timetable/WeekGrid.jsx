import { useDraggable, useDroppable } from '@dnd-kit/core';
import { CSS } from '@dnd-kit/utilities';
import { Button } from '@wordpress/components';
import { byStart, WEEKDAYS } from './utils';

function EventChip({ item, onAdd }) {
  const { attributes, listeners, setNodeRef, transform, isDragging } = useDraggable({
    id: `event:${item.id}`,
    data: { type: 'event', eventId: item.id },
  });

  return (
    <li className="bbb-tt-palette__item">
      <span
        ref={setNodeRef}
        className={`bbb-tt-chip${isDragging ? ' is-dragging' : ''}`}
        style={{ '--chip': item.color, transform: CSS.Translate.toString(transform) }}
        {...listeners}
        {...attributes}
        aria-roledescription="draggable event"
        aria-label={`${item.title}. Drag onto a day, or use Add.`}
      >
        {item.title}
      </span>
      <Button size="small" variant="link" onClick={() => onAdd(item.id)} aria-label={`Add a ${item.title} session`}>
        Add
      </Button>
    </li>
  );
}

export function Palette({ events, search, onSearch, onAdd }) {
  const shown = events.filter((c) => c.title.toLowerCase().includes(search.toLowerCase()));

  return (
    <aside className="bbb-tt-palette" aria-labelledby="bbb-tt-palette-title">
      <h2 id="bbb-tt-palette-title">Events</h2>
      <p className="bbb-tt-hint">Drag an event onto a day, or press Add.</p>
      {events.length > 6 && (
        <input
          type="search"
          className="bbb-tt-palette__search"
          placeholder="Find an event"
          aria-label="Find an event"
          value={search}
          onChange={(e) => onSearch(e.target.value)}
        />
      )}
      <ul>
        {shown.map((item) => (
          <EventChip key={item.id} item={item} onAdd={onAdd} />
        ))}
      </ul>
      {!events.length && <p className="bbb-tt-hint">No published events in this category yet.</p>}
    </aside>
  );
}

function SessionCard({ session, item, problems, cancellations, onEdit, onJump }) {
  const { attributes, listeners, setNodeRef, transform, isDragging } = useDraggable({
    id: `session:${session.id}`,
    data: { type: 'session', sessionId: session.id },
  });
  const label = `${session.start} to ${session.end}, ${item?.title ?? 'missing event'}`;

  return (
    <li
      ref={setNodeRef}
      data-ref={session.id}
      className={`bbb-tt-card${isDragging ? ' is-dragging' : ''}${problems.length ? ' has-problem' : ''}${item ? '' : ' is-missing'}`}
      style={{ '--chip': item?.color ?? '#757575', transform: CSS.Translate.toString(transform) }}
    >
      <button type="button" className="bbb-tt-card__button" onClick={() => onEdit(session)} aria-label={`Edit ${label}`}>
        <span className="bbb-tt-card__time">
          {session.start}–{session.end}
        </span>
        <span className="bbb-tt-card__title">{item?.title ?? 'Event no longer available'}</span>
        {problems.map((p) => (
          <span key={p.message} className="bbb-tt-card__problem">
            {p.message}
          </span>
        ))}
      </button>
      {cancellations.length > 0 && (
        <button type="button" className="bbb-tt-card__cancelled" onClick={() => onJump(cancellations[0].id)} aria-label={`${cancellations.length} cancelled date${cancellations.length === 1 ? '' : 's'} for ${label}. Go to cancellations.`}>
          {cancellations.length} cancelled
        </button>
      )}
      <span className="bbb-tt-card__handle" {...listeners} {...attributes} aria-roledescription="draggable session" aria-label={`Move ${label}`}>
        ⠿
      </span>
    </li>
  );
}

function DayColumn({ day, sessions, eventsById, problemsFor, cancellationsFor, onEdit, onJump, onAdd }) {
  const { setNodeRef, isOver } = useDroppable({ id: `day:${day.n}`, data: { weekday: day.n } });

  return (
    <section ref={setNodeRef} className={`bbb-tt-day${isOver ? ' is-over' : ''}`} aria-labelledby={`bbb-tt-day-${day.n}`}>
      <h3 id={`bbb-tt-day-${day.n}`}>{day.label}</h3>
      {sessions.length ? (
        <ul>
          {sessions.map((s) => (
            <SessionCard key={s.id} session={s} item={eventsById[s.event]} problems={problemsFor(s.id)} cancellations={cancellationsFor(s.id)} onEdit={onEdit} onJump={onJump} />
          ))}
        </ul>
      ) : (
        <p className="bbb-tt-day__empty">No sessions</p>
      )}
      <Button className="bbb-tt-day__add" variant="secondary" size="small" onClick={() => onAdd(day.n)}>
        + Add session
      </Button>
    </section>
  );
}

export function WeekGrid({ sessions, eventsById, problemsFor, cancellationsFor, onEdit, onJump, onAdd }) {
  return (
    <div className="bbb-tt-grid" role="group" aria-label="Weekly timetable">
      {WEEKDAYS.map((day) => (
        <DayColumn
          key={day.n}
          day={day}
          sessions={sessions.filter((s) => s.weekday === day.n).sort(byStart)}
          eventsById={eventsById}
          cancellationsFor={cancellationsFor}
          onJump={onJump}
          problemsFor={problemsFor}
          onEdit={onEdit}
          onAdd={onAdd}
        />
      ))}
    </div>
  );
}
