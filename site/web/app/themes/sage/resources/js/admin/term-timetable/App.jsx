import { DndContext, KeyboardSensor, PointerSensor, useSensor, useSensors } from '@dnd-kit/core';
import { Button, Notice, SelectControl, Spinner, TextControl } from '@wordpress/components';
import { useEffect, useMemo, useRef, useState } from '@wordpress/element';
import { speak } from '@wordpress/a11y';
import ExceptionsPanel from './ExceptionsPanel';
import SessionModal from './SessionModal';
import { Palette, WeekGrid } from './WeekGrid';
import { addMinutes, api, dayLabel, DEFAULT_LENGTH, emptySchedule, friendlyDate, META, nextFreeStart, uid, weeksIn } from './utils';

const config = window.bbbTermTimetable;

export default function App() {
  const [termId, setTermId] = useState(config.termId);
  const [title, setTitle] = useState('');
  const [status, setStatus] = useState('draft');
  const [schedule, setSchedule] = useState(emptySchedule(config.defaultCategory));
  const [loading, setLoading] = useState(!!config.termId);
  const [dirty, setDirty] = useState(false);
  const [saving, setSaving] = useState(false);
  const [notice, setNotice] = useState(null);
  const [categories, setCategories] = useState([]);
  const [classes, setClasses] = useState([]);
  const [search, setSearch] = useState('');
  const [preview, setPreview] = useState(null);
  const [modal, setModal] = useState(null);
  const [otherTerms, setOtherTerms] = useState([]);
  const altKey = useRef(false);

  const classesById = useMemo(() => Object.fromEntries(classes.map((c) => [c.id, c])), [classes]);

  const change = (patch) => {
    setSchedule((s) => ({ ...s, ...patch }));
    setDirty(true);
  };

  // Load the term (if editing), the categories, and other terms to copy from.
  useEffect(() => {
    api.categories().then(setCategories);
    api.terms().then((terms) => setOtherTerms(terms.filter((t) => t.id !== config.termId)));
    if (!config.termId) return;
    api
      .load(config.termId)
      .then((term) => {
        setTitle(term.title.raw);
        setStatus(term.status);
        setSchedule({ ...emptySchedule(config.defaultCategory), ...(term.meta?.[META] ?? {}) });
      })
      .catch((e) => setNotice({ status: 'error', message: e.message }))
      .finally(() => setLoading(false));
  }, []);

  useEffect(() => {
    api.classes(schedule.category).then(setClasses);
  }, [schedule.category]);

  // What saving would create, refreshed shortly after each change.
  useEffect(() => {
    const timer = setTimeout(() => api.preview(schedule, termId).then(setPreview).catch(() => {}), 400);
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
  const termProblems = problems.filter((p) => p.ref === 'dates' || String(p.ref).startsWith('event:'));
  const hasErrors = (preview?.errors ?? []).length > 0;

  const save = async (nextStatus) => {
    setSaving(true);
    setNotice(null);
    try {
      const saved = await api.save(termId, { title: title || 'Untitled term', status: nextStatus, meta: { [META]: schedule } });
      setTermId(saved.id);
      setStatus(saved.status);
      setDirty(false);
      if (!termId) window.history.replaceState(null, '', `${config.editUrl}&term=${saved.id}`);
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
    speak(`${classesById[session.event]?.title} ${exists ? 'updated' : 'added'}: ${dayLabel(session.weekday)} ${session.start}.`);
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

  // Drag and drop: a class onto a day opens Add for that day; a session onto
  // another day moves it (or copies it with Alt), keeping its times.
  const sensors = useSensors(useSensor(PointerSensor, { activationConstraint: { distance: 5 } }), useSensor(KeyboardSensor));
  const onDragEnd = ({ active, over }) => {
    const weekday = over?.data.current?.weekday;
    if (!weekday) return;
    const data = active.data.current;
    if (data.type === 'class') {
      openAdd(weekday, data.classId);
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
    if (data?.type === 'class') return classesById[data.classId]?.title ?? 'class';
    const s = schedule.sessions.find((x) => x.id === data?.sessionId);
    return s ? `${classesById[s.event]?.title ?? 'session'} at ${s.start}` : 'item';
  };
  const announcements = {
    onDragStart: ({ active }) => `Picked up ${describe(active)}. Use the arrow keys to move over a day, Space to drop, Escape to cancel.`,
    onDragOver: ({ active, over }) => (over ? `${describe(active)} is over ${dayLabel(over.data.current?.weekday)}.` : `${describe(active)} is not over a day.`),
    onDragEnd: ({ active, over }) => (over ? `Dropped ${describe(active)} on ${dayLabel(over.data.current?.weekday)}.` : `${describe(active)} was dropped outside the timetable.`),
    onDragCancel: ({ active }) => `Cancelled moving ${describe(active)}.`,
  };

  const copyFrom = (id) => {
    const source = otherTerms.find((t) => String(t.id) === String(id));
    if (!source) return;
    const s = { ...emptySchedule(), ...(source.meta?.[META] ?? {}) };
    change({ category: s.category, sessions: s.sessions.map((x) => ({ ...x, id: uid() })), cancellations: [], extras: [] });
    speak(`Copied ${s.sessions.length} weekly sessions from ${source.title.raw}. Set the new term's dates.`);
  };

  if (loading) return <Spinner />;

  const classCount = Object.keys(preview?.counts ?? {}).length;

  return (
    <div className="bbb-tt">
      <header className="bbb-tt-header">
        <div className="bbb-tt-header__top">
          <a href={config.listUrl} className="bbb-tt-back">
            ← All term timetables
          </a>
          <span className={`bbb-tt-status is-${status}`}>{status === 'publish' ? 'Published' : 'Draft'}</span>
        </div>
        <div className="bbb-tt-header__fields">
          <TextControl __nextHasNoMarginBottom label="Term name" placeholder="e.g. Spring term 2027" value={title} onChange={(v) => (setTitle(v), setDirty(true))} />
          <TextControl __nextHasNoMarginBottom label="First day" type="date" value={schedule.start} onChange={(start) => change({ start, end: schedule.end && schedule.end < start ? start : schedule.end })} />
          <TextControl __nextHasNoMarginBottom label="Last day" type="date" value={schedule.end} min={schedule.start} onChange={(end) => change({ end })} />
          <SelectControl
            __nextHasNoMarginBottom
            label="Classes from"
            value={schedule.category}
            options={[{ value: 0, label: 'All event categories' }, ...categories.map((c) => ({ value: c.id, label: c.name }))]}
            onChange={(category) => change({ category: Number(category) })}
          />
        </div>
        {!termId && !schedule.sessions.length && otherTerms.length > 0 && (
          <SelectControl
            __nextHasNoMarginBottom
            className="bbb-tt-copy"
            label="Start from an existing term (copies its weekly sessions)"
            value=""
            options={[{ value: '', label: 'Start from scratch' }, ...otherTerms.map((t) => ({ value: t.id, label: t.title.raw || `Term #${t.id}` }))]}
            onChange={copyFrom}
          />
        )}
      </header>

      {config.lockedBy && (
        <Notice status="warning" isDismissible={false}>
          {config.lockedBy} is also editing this term. Saving may overwrite their changes.
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
            across {classCount} class{classCount === 1 ? '' : 'es'}
            {schedule.start && schedule.end && ` over ${weeksIn(schedule.start, schedule.end)} weeks (${friendlyDate(schedule.start)} – ${friendlyDate(schedule.end)})`}
            {status !== 'publish' && ' — added to the calendar when you publish.'}
          </p>
        ) : (
          <p>Add the term's dates and weekly sessions.</p>
        )}
        {termProblems.map((p) => (
          <Notice key={p.message} status={p.level} isDismissible={false}>
            {p.message}
          </Notice>
        ))}
        {hasErrors && !termProblems.some((p) => p.level === 'error') && (
          <Notice status="error" isDismissible={false}>
            Some sessions need fixing before you can publish — they're outlined in red below.
          </Notice>
        )}
      </div>

      <DndContext sensors={sensors} onDragEnd={onDragEnd} accessibility={{ announcements }}>
        <div className="bbb-tt-body">
          <Palette classes={classes} search={search} onSearch={setSearch} onAdd={(classId) => openAdd(6, classId)} />
          <WeekGrid sessions={schedule.sessions} classesById={classesById} problemsFor={problemsFor} onEdit={(session) => setModal({ isNew: false, session })} onAdd={(weekday) => openAdd(weekday)} />
        </div>
      </DndContext>
      <p className="bbb-tt-hint">Tip: hold Alt (Option on a Mac) while dropping a session to copy it to another day.</p>

      <ExceptionsPanel schedule={schedule} classes={classes} classesById={classesById} problemsFor={problemsFor} onChange={change} />

      <footer className="bbb-tt-footer">
        <Button
          variant="secondary"
          disabled={saving}
          onClick={() =>
            // Unpublishing takes every session this term created out of the calendar.
            (status !== 'publish' || window.confirm('Switch to draft? This removes all of this term\'s sessions from the events calendar until you publish again.')) && save('draft')
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
      </footer>

      {modal && (
        <SessionModal
          session={modal.session}
          isNew={modal.isNew}
          classes={classes}
          onSave={saveSession}
          onDelete={deleteSession}
          onDuplicate={duplicateSession}
          onClose={() => setModal(null)}
        />
      )}
    </div>
  );
}
