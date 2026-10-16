import { Button, Modal, Notice, SelectControl, TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { WEEKDAYS } from './utils';

/**
 * Add or edit one weekly session. Also the click/keyboard route for
 * everything drag and drop does (WCAG 2.5.7): the class and day can be
 * changed here, and Duplicate copies a session.
 */
export default function SessionModal({ session, isNew, classes, onSave, onDelete, onDuplicate, onClose }) {
  const [draft, setDraft] = useState(session);
  const set = (key) => (value) => setDraft({ ...draft, [key]: key === 'event' || key === 'weekday' ? Number(value) : value });

  const problem = !draft.event
    ? 'Choose a class.'
    : !draft.start || !draft.end
      ? 'Set a start and end time.'
      : draft.end <= draft.start
        ? 'The session must end after it starts.'
        : null;

  const submit = (e) => {
    e.preventDefault();
    if (!problem) onSave(draft);
  };

  return (
    <Modal title={isNew ? 'Add a weekly session' : 'Edit session'} onRequestClose={onClose} className="bbb-tt-modal">
      <form onSubmit={submit}>
        <SelectControl
          __nextHasNoMarginBottom
          label="Class"
          value={draft.event || ''}
          options={[{ value: '', label: 'Choose a class…' }, ...classes.map((c) => ({ value: c.id, label: c.title }))]}
          onChange={set('event')}
        />
        <SelectControl
          __nextHasNoMarginBottom
          label="Day"
          value={draft.weekday}
          options={WEEKDAYS.map((d) => ({ value: d.n, label: d.label }))}
          onChange={set('weekday')}
        />
        <div className="bbb-tt-modal__times">
          <TextControl __nextHasNoMarginBottom label="Starts" type="time" step={300} value={draft.start} onChange={set('start')} />
          <TextControl __nextHasNoMarginBottom label="Ends" type="time" step={300} value={draft.end} onChange={set('end')} />
        </div>
        {problem && draft.event !== undefined && (
          <Notice status="warning" isDismissible={false}>
            {problem}
          </Notice>
        )}
        <div className="bbb-tt-modal__actions">
          <Button variant="primary" type="submit" disabled={!!problem}>
            {isNew ? 'Add session' : 'Save'}
          </Button>
          {!isNew && (
            <>
              <Button variant="secondary" onClick={() => onDuplicate(draft)} disabled={!!problem}>
                Duplicate
              </Button>
              <Button variant="tertiary" isDestructive onClick={() => onDelete(draft)}>
                Delete
              </Button>
            </>
          )}
          <Button variant="tertiary" onClick={onClose}>
            Cancel
          </Button>
        </div>
      </form>
    </Modal>
  );
}
