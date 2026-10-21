import { DndContext, KeyboardSensor, PointerSensor, useSensor, useSensors } from '@dnd-kit/core';
import { Button, Notice, SelectControl, Spinner, TextControl } from '@wordpress/components';
import { useEffect, useMemo, useRef, useState } from '@wordpress/element';
import { speak } from '@wordpress/a11y';
import ExceptionsPanel from './ExceptionsPanel';
import HelpPanel from './HelpPanel';
import SessionModal from './SessionModal';
import { Palette, WeekGrid } from './WeekGrid';
import { addMinutes, api, dayLabel, DEFAULT_LENGTH, emptySchedule, friendlyDate, META, nextFreeStart, uid, weeksIn } from './utils';

const config = window.bbbEventTimetable;

export default function App() {
  const [timetableId, setTimetableId] = useState(config.timetableId);
  const [title, setTitle] = useState('');
  const [status, setStatus] = useState('draft');
  const [schedule, setSchedule] = useState(emptySchedule(config.defaultCategory));
  const [loading, setLoading] = useState(!!config.timetableId);
  const [dirty, setDirty] = useState(false);
  const [saving, setSaving] = useState(false);
  const [notice, setNotice] = useState(null);
  const [categories, setCategories] = useState([]);
  const [events, setEvents] = useState([]);
  const [search, setSearch] = useState('');
  const [preview, setPreview] = useState(null);
  const [modal, setModal] = useState(null);
  const [otherTimetables, setOtherTimetables] = useState([]);
  const altKey = useRef(false);

  const eventsById = useMemo(() => Object.fromEntries(events.map((c) => [c.id, c])), [events]);

  const change = (patch) => {
    setSchedule((s) => ({ ...s, ...patch }));
    setDirty(true);
  };

  // Load the timetable (if editing), the categories, and other timetables to copy from.
  useEffect(() => {
    api.categories().then(setCategories);
    api.timetables().then((timetables) => setOtherTimetables(timetables.filter((t) => t.id !== config.timetableId)));
    if (!config.timetableId) return;
    api
      .load(config.timetableId)
      .then((timetable) => {
        setTitle(timetable.title.raw);
        setStatus(timetable.status);
        setSchedule({ ...emptySchedule(config.defaultCategory), ...(timetable.meta?.[META] ?? {}) });
      })
      .catch((e) => setNotice({ status: 'error', message: e.message }))
      .finally(() => setLoading(false));
  }, []);

  useEffect(() => {
    api.events(schedule.category).then(setEvents);
  }, [schedule.category]);

  // What saving would create, refreshed shortly after each change.
  useEffect(() => {
    const timer = setTimeout(() => api.preview(schedule, timetableId).then(setPreview).catch(() => {}), 400);
    return () => clearTimeout(timer);
  }, [schedule]);

  // Alt held while dropping a session copies it instead of moving it.
  useEffect(() => {
    const track = (e) => (altKey.current = e.altKey);
    window.addEventListener('keydown', track);
    window.addEventListener('keyup', track);
    return () => {
      window.removeEventListener('keydown', track);
      window.removeEventListener('keyup', track);
    };
  }, []);

  useEffect(() => {
    const warn = (e) => {
      if (dirty) {
        e.preventDefault();
        e.returnValue = '';
      }
    };
    window.addEventListener('beforeunload', warn);
    return () => window.removeEventListener('beforeunload', warn);
  }, [dirty]);

  const problems = [...(preview?.errors ?? []).map((p) => ({ ...p, level: 'error' })), ...(preview?.warnings ?? []).map((p) => ({ ...p, level: 'warning' }))];
  const problemsFor = (id) => problems.filter((p) => p.ref === id);
  const timetableProblems = problems.filter((p) => p.ref === 'dates' || String(p.ref).startsWith('event:'));
  const hasErrors = (preview?.errors ?? []).length > 0;
  const warningCount = (preview?.warnings ?? []).length;

  // Scroll to, focus and briefly highlight the first error (or warning).
  const showFirstProblem = () => {
    const first = problems.find((p) => p.level === 'error') ?? problems[0];
    if (!first) return;
    const ref = String(first.ref).startsWith('event:') ? null : first.ref;
    const el = (ref && document.querySelector(`[data-ref="${CSS.escape(ref)}"]`)) || document.querySelector('.bbb-tt-summary');
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    (el.querySelector('button, input, select') ?? el).focus({ preventScroll: true });
    el.classList.add('is-flagged');
    setTimeout(() => el.classList.remove('is-flagged'), 2000);
    speak(first.message);
  };
  const errorCount = (preview?.errors ?? []).length;

  const cancellationsFor = (sessionId) => schedule.cancellations.filter((c) => c.session === sessionId);
  // From a session's "cancelled" badge to its row in the Cancelled sessions list.
  const jumpTo = (ref) => {
    const el = document.querySelector(`[data-ref="${CSS.escape(ref)}"]`);
    if (!el) return;
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    (el.querySelector('button, input, select') ?? el).focus({ preventScroll: true });
    el.classList.add('is-flagged');
    setTimeout(() => el.classList.remove('is-flagged'), 2000);
  };

  const save = async (nextStatus) => {
    setSaving(true);
    setNotice(null);
    try {
      const saved = await api.save(timetableId, { title: title || 'Untitled timetable', status: nextStatus, meta: { [META]: schedule } });
      setTimetableId(saved.id);
      setStatus(saved.status);
      setDirty(false);
      if (!timetableId) window.history.replaceState(null, '', `${config.editUrl}&timetable=${saved.id}`);
      const message = saved.status === 'publish' ? 'Published — the sessions are now in the events calendar.' : 'Draft saved. Nothing is added to the calendar until you publish.';
      setNotice({ status: 'success', message });
      speak(message);
    } catch (e) {
      setNotice({ status: 'error', message: e.message });
      speak(e.message, 'assertive');
    } finally {
      setSaving(false);
    }
  };

  // Sessions
  const openAdd = (weekday = 6, event = 0) => {
    const start = nextFreeStart(schedule.sessions, weekday);
    setModal({ isNew: true, session: { id: uid(), event, weekday, start, end: addMinutes(start, DEFAULT_LENGTH) } });
  };
  const saveSession = (session) => {
    const exists = schedule.sessions.some((s) => s.id === session.id);
    change({ sessions: exists ? schedule.sessions.map((s) => (s.id === session.id ? session : s)) : [...schedule.sessions, session] });
    speak(`${eventsById[session.event]?.title} ${exists ? 'updated' : 'added'}: ${dayLabel(session.weekday)} ${session.start}.`);
    setModal(null);
  };
  const deleteSession = (session) => {
    change({
      sessions: schedule.sessions.filter((s) => s.id !== session.id),
      cancellations: schedule.cancellations.filter((c) => c.session !== session.id),
    });
    speak('Session deleted.');
    setModal(null);
  };
  const duplicateSession = (session) => {
    change({ sessions: [...schedule.sessions, { ...session, id: uid() }] });
    speak('Session duplicated.');
    setModal(null);
  };

  // Drag and drop: an event onto a day opens Add for that day; a session onto
  // another day moves it (or copies it with Alt), keeping its times.
  const sensors = useSensors(useSensor(PointerSensor, { activationConstraint: { distance: 5 } }), useSensor(KeyboardSensor));
  const onDragEnd = ({ active, over }) => {
    const weekday = over?.data.current?.weekday;
    if (!weekday) return;
    const data = active.data.current;
    if (data.type === 'event') {
      openAdd(weekday, data.eventId);
      return;
    }
    const session = schedule.sessions.find((s) => s.id === data.sessionId);
    if (!session || session.weekday === weekday) return;
    if (altKey.current) {
      change({ sessions: [...schedule.sessions, { ...session, id: uid(), weekday }] });
    } else {
      change({ sessions: schedule.sessions.map((s) => (s.id === session.id ? { ...s, weekday } : s)) });
    }
  };
  const describe = (active) => {
    const data = active?.data.current;
    if (data?.type === 'event') return eventsById[data.eventId]?.title ?? 'event';
    const s = schedule.sessions.find((x) => x.id === data?.sessionId);
    return s ? `${eventsById[s.event]?.title ?? 'session'} at ${s.start}` : 'item';
  };
  const announcements = {
    onDragStart: ({ active }) => `Picked up ${describe(active)}. Use the arrow keys to move over a day, Space to drop, Escape to cancel.`,
    onDragOver: ({ active, over }) => (over ? `${describe(active)} is over ${dayLabel(over.data.current?.weekday)}.` : `${describe(active)} is not over a day.`),
    onDragEnd: ({ active, over }) => (over ? `Dropped ${describe(active)} on ${dayLabel(over.data.current?.weekday)}.` : `${describe(active)} was dropped outside the timetable.`),
    onDragCancel: ({ active }) => `Cancelled moving ${describe(active)}.`,
  };

  const copyFrom = (id) => {
    const source = otherTimetables.find((t) => String(t.id) === String(id));
    if (!source) return;
    const s = { ...emptySchedule(), ...(source.meta?.[META] ?? {}) };
    change({ category: s.category, sessions: s.sessions.map((x) => ({ ...x, id: uid() })), cancellations: [], extras: [] });
    speak(`Copied ${s.sessions.length} weekly sessions from ${source.title.raw}. Set the new timetable's dates.`);
  };

  if (loading) return <Spinner />;

  const eventCount = Object.keys(preview?.counts ?? {}).length;

  return (
    <div className="bbb-tt">
      <HelpPanel isNew={!config.timetableId} />
      <header className="bbb-tt-header">
        <div className="bbb-tt-header__top">
          <a href={config.listUrl} className="bbb-tt-back">
            ← All event timetables
          </a>
          <span className={`bbb-tt-status is-${status}`}>{status === 'publish' ? 'Published' : 'Draft'}</span>
        </div>
        <div className="bbb-tt-header__fields" data-ref="dates">
          <TextControl __nextHasNoMarginBottom label="Timetable name" placeholder="e.g. Spring term 2027" value={title} onChange={(v) => (setTitle(v), setDirty(true))} />
          <TextControl __nextHasNoMarginBottom label="First day" type="date" value={schedule.start} onChange={(start) => change({ start, end: schedule.end && schedule.end < start ? start : schedule.end })} />
          <TextControl __nextHasNoMarginBottom label="Last day" type="date" value={schedule.end} min={schedule.start} onChange={(end) => change({ end })} />
          <SelectControl
            __nextHasNoMarginBottom
            label="Event categories"
            value={schedule.category}
            options={[{ value: 0, label: 'All event categories' }, ...categories.map((c) => ({ value: c.id, label: c.name }))]}
            onChange={(category) => change({ category: Number(category) })}
          />
        </div>
        {!timetableId && !schedule.sessions.length && otherTimetables.length > 0 && (
          <SelectControl
            __nextHasNoMarginBottom
            className="bbb-tt-copy"
            label="Start from an existing timetable (copies its weekly sessions)"
            value=""
            options={[{ value: '', label: 'Start from scratch' }, ...otherTimetables.map((t) => ({ value: t.id, label: t.title.raw || `Timetable #${t.id}` }))]}
            onChange={copyFrom}
          />
        )}
      </header>

      {config.lockedBy && (
        <Notice status="warning" isDismissible={false}>
          {config.lockedBy} is also editing this timetable. Saving may overwrite their changes.
        </Notice>
      )}
      {notice && (
        <Notice status={notice.status} onRemove={() => setNotice(null)}>
          {notice.message}
        </Notice>
      )}

      <div className="bbb-tt-summary" aria-live="polite">
        {preview ? (
          <p>
            <strong>
              {preview.total} session{preview.total === 1 ? '' : 's'}
            </strong>{' '}
            across {eventCount} event{eventCount === 1 ? '' : 's'}
            {schedule.start && schedule.end && ` over ${weeksIn(schedule.start, schedule.end)} weeks (${friendlyDate(schedule.start)} – ${friendlyDate(schedule.end)})`}
            {status !== 'publish' && ' — added to the calendar when you publish.'}
          </p>
        ) : (
          <p>Add the timetable's dates and weekly sessions.</p>
        )}
        {timetableProblems.map((p) => (
          <Notice key={p.message} status={p.level} isDismissible={false}>
            {p.message}
          </Notice>
        ))}
        {hasErrors && !timetableProblems.some((p) => p.level === 'error') && (
          <Notice status="error" isDismissible={false}>
            Some sessions need fixing before you can publish — they're outlined in red below.
          </Notice>
        )}
      </div>

      <DndContext sensors={sensors} onDragEnd={onDragEnd} accessibility={{ announcements }}>
        <div className="bbb-tt-body">
          <Palette events={events} search={search} onSearch={setSearch} onAdd={(eventId) => openAdd(6, eventId)} />
          <WeekGrid sessions={schedule.sessions} eventsById={eventsById} problemsFor={problemsFor} cancellationsFor={cancellationsFor} onJump={jumpTo} onEdit={(session) => setModal({ isNew: false, session })} onAdd={(weekday) => openAdd(weekday)} />
        </div>
      </DndContext>
      <p className="bbb-tt-hint">Tip: hold Alt (Option on a Mac) while dropping a session to copy it to another day.</p>

      <ExceptionsPanel schedule={schedule} events={events} eventsById={eventsById} problemsFor={problemsFor} onChange={change} />

      <footer className="bbb-tt-footer">
        <Button
          variant="secondary"
          disabled={saving}
          onClick={() =>
            // Unpublishing takes every session this timetable created out of the calendar.
            (status !== 'publish' || window.confirm('Switch to draft? This removes all of this timetable\'s sessions from the events calendar until you publish again.')) && save('draft')
          }
        >
          {status === 'publish' ? 'Switch to draft' : 'Save draft'}
        </Button>
        {config.canPublish && (
          <Button variant="primary" onClick={() => save('publish')} disabled={saving || hasErrors} isBusy={saving}>
            {status === 'publish' ? 'Update' : 'Publish'}
          </Button>
        )}
        {dirty && <span className="bbb-tt-unsaved">Unsaved changes</span>}
        {(errorCount > 0 || warningCount > 0) && (
          <span className={`bbb-tt-outstanding is-${errorCount ? 'error' : 'warning'}`} role="status">
            {errorCount
              ? `Can’t ${status === 'publish' ? 'update' : 'publish'} yet — ${errorCount} problem${errorCount === 1 ? '' : 's'} to fix.`
              : `${warningCount} warning${warningCount === 1 ? '' : 's'} to check — you can still ${status === 'publish' ? 'update' : 'publish'}.`}
            <Button variant="link" onClick={showFirstProblem}>
              Show me
            </Button>
          </span>
        )}
      </footer>

      {modal && (
        <SessionModal
          session={modal.session}
          isNew={modal.isNew}
          events={events}
          onSave={saveSession}
          onDelete={deleteSession}
          onDuplicate={duplicateSession}
          onClose={() => setModal(null)}
        />
      )}
    </div>
  );
}
