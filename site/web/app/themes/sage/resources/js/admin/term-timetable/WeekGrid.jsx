import { useDraggable, useDroppable } from '@dnd-kit/core';
import { CSS } from '@dnd-kit/utilities';
import { Button } from '@wordpress/components';
import { byStart, WEEKDAYS } from './utils';

function ClassChip({ cls, onAdd }) {
  const { attributes, listeners, setNodeRef, transform, isDragging } = useDraggable({
    id: `class:${cls.id}`,
    data: { type: 'class', classId: cls.id },
  });

  return (
    <li className="bbb-tt-palette__item">
      <span
        ref={setNodeRef}
        className={`bbb-tt-chip${isDragging ? ' is-dragging' : ''}`}
        style={{ '--chip': cls.color, transform: CSS.Translate.toString(transform) }}
        {...listeners}
        {...attributes}
        aria-roledescription="draggable class"
        aria-label={`${cls.title}. Drag onto a day, or use Add.`}
      >
        {cls.title}
      </span>
      <Button size="small" variant="link" onClick={() => onAdd(cls.id)} aria-label={`Add a ${cls.title} session`}>
        Add
      </Button>
    </li>
  );
}

export function Palette({ classes, search, onSearch, onAdd }) {
  const shown = classes.filter((c) => c.title.toLowerCase().includes(search.toLowerCase()));

  return (
    <aside className="bbb-tt-palette" aria-labelledby="bbb-tt-palette-title">
      <h2 id="bbb-tt-palette-title">Classes</h2>
      <p className="bbb-tt-hint">Drag a class onto a day, or press Add.</p>
      {classes.length > 6 && (
        <input
          type="search"
          className="bbb-tt-palette__search"
          placeholder="Find a class"
          aria-label="Find a class"
          value={search}
          onChange={(e) => onSearch(e.target.value)}
        />
      )}
      <ul>
        {shown.map((cls) => (
          <ClassChip key={cls.id} cls={cls} onAdd={onAdd} />
        ))}
      </ul>
      {!classes.length && <p className="bbb-tt-hint">No published classes in this category yet.</p>}
    </aside>
  );
}

function SessionCard({ session, cls, problems, onEdit }) {
  const { attributes, listeners, setNodeRef, transform, isDragging } = useDraggable({
    id: `session:${session.id}`,
    data: { type: 'session', sessionId: session.id },
  });
  const label = `${session.start} to ${session.end}, ${cls?.title ?? 'missing class'}`;

  return (
    <li
      ref={setNodeRef}
      data-ref={session.id}
      className={`bbb-tt-card${isDragging ? ' is-dragging' : ''}${problems.length ? ' has-problem' : ''}${cls ? '' : ' is-missing'}`}
      style={{ '--chip': cls?.color ?? '#757575', transform: CSS.Translate.toString(transform) }}
    >
      <button type="button" className="bbb-tt-card__button" onClick={() => onEdit(session)} aria-label={`Edit ${label}`}>
        <span className="bbb-tt-card__time">
          {session.start}–{session.end}
        </span>
        <span className="bbb-tt-card__title">{cls?.title ?? 'Class no longer available'}</span>
        {problems.map((p) => (
          <span key={p.message} className="bbb-tt-card__problem">
            {p.message}
          </span>
        ))}
      </button>
      <span className="bbb-tt-card__handle" {...listeners} {...attributes} aria-roledescription="draggable session" aria-label={`Move ${label}`}>
        ⠿
      </span>
    </li>
  );
}

function DayColumn({ day, sessions, classesById, problemsFor, onEdit, onAdd }) {
  const { setNodeRef, isOver } = useDroppable({ id: `day:${day.n}`, data: { weekday: day.n } });

  return (
    <section ref={setNodeRef} className={`bbb-tt-day${isOver ? ' is-over' : ''}`} aria-labelledby={`bbb-tt-day-${day.n}`}>
      <h3 id={`bbb-tt-day-${day.n}`}>{day.label}</h3>
      {sessions.length ? (
        <ul>
          {sessions.map((s) => (
            <SessionCard key={s.id} session={s} cls={classesById[s.event]} problems={problemsFor(s.id)} onEdit={onEdit} />
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

export function WeekGrid({ sessions, classesById, problemsFor, onEdit, onAdd }) {
  return (
    <div className="bbb-tt-grid" role="group" aria-label="Weekly timetable">
      {WEEKDAYS.map((day) => (
        <DayColumn
          key={day.n}
          day={day}
          sessions={sessions.filter((s) => s.weekday === day.n).sort(byStart)}
          classesById={classesById}
          problemsFor={problemsFor}
          onEdit={onEdit}
          onAdd={onAdd}
        />
      ))}
    </div>
  );
}
