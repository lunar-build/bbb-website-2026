# Event timetables

A timetable is a weekly pattern of sessions (event + weekday + start/end) over a date range, edited on a drag-and-drop screen
(`resources/js/admin/event-timetable`) and written into each event's **MEC Custom Days** when it is published. The front end
(What's on, the event page) reads MEC's dates; the timetable stays the source of truth for what was cancelled or closed.

## Files

| File | Job |
|---|---|
| `EventTimetable.php` | Post type, admin page, list-table columns. Boots the rest. |
| `Schedule.php` | Plain PHP (no WordPress), so it is unit tested. Normalises a schedule, validates it, expands it into dated sessions, parses MEC day strings, finds the reason a session is off. |
| `MecSync.php` | Writes a published timetable's sessions into each event's MEC dates. Idempotent. Also works out which sessions a save drops (`removedDays`). |
| `BookingCancellations.php` | Cancels the bookings on dropped sessions through MEC, emails the bookers and sends the admin one summary. |
| `CancelledSessions.php` | An event's upcoming cancelled/closed sessions, for the event page. |
| `RestController.php` | `bbb/v1` routes for the editor, including `timetable-preview` (what saving will do, now including affected bookings). |
| `EventEditGuard.php`, `EventDeletionWarnings.php` | Warn when MEC's own event screen would fight the timetable. |

## What cancelling does

Saving a **published** timetable that stops running a session people have booked **cancels those bookings**:

1. `MecSync::sync()` works out `removedDays()`: sessions this timetable ran last time (`_timetable_generated_days`) and no longer
   generates, minus any another published timetable still generates, and minus events it can't change (deleted, or on their own MEC repeat rule).
2. `BookingCancellations::sessions()` turns those into rows with the reason (`Schedule::reasonFor`: the cancellation's reason, else
   the closure's message, else blank) and drops sessions that have already started.
3. `BookingCancellations::cancel()` finds each session's live bookings (`bookingsFor`: not cancelled or rejected, not in the trash,
   `all_occurrences = 0`, matching the event and the session's start timestamp) and calls MEC's `$book->cancel($id, false)`.
4. MEC then sends its cancellation email to each booker. A `%%cancellation_reason%%` placeholder (added in `BookingCancellations::boot`)
   renders as "Reason: …" and is in the seeded wording; it is blank when no reason was given.
5. While a batch runs, MEC's admin and organiser copies are switched off (an `option_mec_options` filter). The admin gets **one**
   summary email listing sessions and bookers instead. Without this, N bookings meant N admin emails, and the booker got two if they shared the admin's address.

What counts as "dropped": a cancelled session, a closure, a deleted or moved session, a time change, shortened dates.

What does **not** cancel bookings: switching to draft, trashing or deleting the timetable (the sessions leave the calendar, the
bookings stay; the editor says so), a first publish, and any save that drops nothing.

**No refunds.** `cancel()` is called with the refund flag off. Add this before paid online gateways (Stripe etc.) go live.

### In the editor

`RestController::preview` returns `bookings` / `bookingsTotal` (only for a saved, published timetable). `App.jsx` shows a warning
panel listing them and asks before saving in an in-page `Modal`. The browser's own `confirm()` is not used: some managed
computers block it, which would leave the button doing nothing.

### On the event page

`CancelledSessions::forEvent()` reads the published timetables for cancellations and closures that fall on this event's
sessions and lists the next few under "Cancelled sessions" (`single-mec-events.blade.php`). The cancelled dates are no longer MEC
dates, so they can't be booked. To say a session has **moved**, put it in the reason ("Moved to Sunday 22 November") and add the new date as an extra session.

An event with every session removed is flagged `_event_undated` and keeps a placeholder date in MEC. `EventSessions::next()` and
`upcoming()` return nothing for those, so the page says no sessions are scheduled and shows no booking form.

## MEC settings this relies on (`mu-plugins/mec-settings.php`)

Forced in code (an `option_mec_options` filter): booking registration **off** (bookers aren't made WordPress users), the
branded email wrapper on, cancellation and rejection emails on and sent to the booker and admin, the confirmation email sent for
auto-confirmed bookings, and UK date and time formats.

The booking **email wording** is *seeded once* per environment (`BBB_MEC_EMAILS_VERSION`) so the client can edit it under Events →
Settings → Notifications. Bump the constant to write it again, which overwrites their edits to those six emails.

Not in code, so set per environment: Booking enabled, auto verification/confirmation, tickets on each event.

## Tests

`composer test` (Pest). `tests/Feature/EventScheduleTest.php` covers `Schedule`: expansion, validation, `parseDay`, `reasonFor`,
the "never runs" warning. The booking side needs WordPress and MEC, so it is checked by hand:

1. Create an event with a free ticket, a published timetable with a weekly session, and a few bookings on one date (the real
   booking form, or copy a `mec-books` post and its `wp_mec_bookings` row).
2. In the editor add a cancellation for that date with a reason. The warning panel should list the bookings.
3. Update, confirm. In Mailpit (https://betterbybike.test:8026): one email per booker with the reason, one admin summary.
4. Events → Bookings shows them Canceled; the event page lists the cancelled session and no longer offers the date.

## Known limits

- Refunds, as above.
- Bookings that cover all dates of an event (`all_occurrences`) have no date of their own and are skipped.
- Times are wall-clock stored as UTC, as MEC does it; there is no time-zone handling.
- Only sessions that haven't started are cancelled.
