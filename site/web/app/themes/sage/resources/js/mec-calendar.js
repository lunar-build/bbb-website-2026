// MEC's monthly view marks days with events using one fixed-colour dot. Swap it for a dot per
// distinct event colour (MEC's single-event colour, shown in the day's event list as .event-color).
// MEC swaps the whole month's markup on month change and filtering, so watch and redo.
const MAX_DOTS = 5; // one per distinct event colour; the dots are small enough for a row of five
const EMPTY_MONTH = window.bbbMec?.emptyMonth ?? 'No events this month.'; // translatable: set by the mec-lunar-theme plugin

// Perceived brightness of an `rgb()` string, 0–1.
const brightness = (colour) => {
  const [r, g, b] = colour.match(/\d+(\.\d+)?/g).map(Number);
  return (0.2126 * r + 0.7152 * g + 0.0722 * b) / 255;
};

const paintDots = (calendar) => {
  calendar.querySelectorAll('.mec-calendar-day.mec-has-event:not([data-dots])').forEach((day) => {
    const events = calendar.querySelector(`.mec-calendar-events-sec[data-mec-cell="${day.dataset.mecCell}"]`);
    if (!events) return;

    // Events with no colour set have a transparent dot; leave those to MEC's own dot.
    const colours = [...new Set([...events.querySelectorAll('.event-color')].map((dot) => getComputedStyle(dot).backgroundColor))].filter(
      (colour) => !/rgba\(.*,\s*0\)/.test(colour),
    );
    day.setAttribute('data-dots', '');
    if (!colours.length) return;

    const overflow = colours.length > MAX_DOTS;
    const dots = document.createElement('span');
    dots.className = 'mec-day-dots';
    dots.setAttribute('aria-hidden', 'true');
    colours.slice(0, MAX_DOTS).forEach((colour) => {
      const dot = document.createElement('span');
      dot.style.backgroundColor = colour;
      // A white or pale dot vanishes on the tile: ring it.
      if (brightness(colour) > 0.82) dot.setAttribute('data-light', '');
      dots.append(dot);
    });
    if (overflow) {
      const more = document.createElement('span');
      more.className = 'mec-day-dots__more';
      more.textContent = '+';
      dots.append(more);
    }
    day.append(dots);
  });
};

// MEC opens a month with the same day-of-month selected as the last one (3rd March, say), whether or not
// anything is on. Open each month on its first day with events instead; with none, say so.
const settleMonth = (calendar) => {
  const month = calendar.querySelector('.mec-month-navigator');
  if (!month || calendar.dataset.settled === month.id) return;
  calendar.dataset.settled = month.id;

  const container = calendar.querySelector('.mec-month-container-selected') ?? calendar;
  const days = [...container.querySelectorAll('.mec-calendar-day.mec-has-event:not(.mec-table-nullday)')];
  const selected = container.querySelector('.mec-calendar-day.mec-selected-day');

  calendar.toggleAttribute('data-empty', !days.length);
  if (!days.length) {
    calendar.querySelectorAll('.mec-calendar-events-sec .mec-event-detail').forEach((detail) => {
      detail.textContent = EMPTY_MONTH;
    });
    return;
  }

  if (!selected?.classList.contains('mec-has-event')) days[0].querySelector('a')?.click();
};

// The full calendar's view tabs (Monthly, Weekly...) are click-only <span>s. Make them reachable and
// operable from the keyboard; MEC's own click handler does the switching.
const enhanceTabs = (wrap) => {
  wrap.querySelectorAll('.mec-totalcal-view span:not([data-tab])').forEach((tab) => {
    tab.dataset.tab = '';
    tab.setAttribute('role', 'button');
    tab.setAttribute('tabindex', '0');
    tab.addEventListener('keydown', (event) => {
      if (event.key !== 'Enter' && event.key !== ' ') return;
      event.preventDefault();
      tab.click();
    });
  });

  wrap.querySelectorAll('.mec-totalcal-view span[data-tab]').forEach((tab) => {
    tab.setAttribute('aria-pressed', String(tab.classList.contains('mec-totalcalview-selected')));
  });
};

// MEC hardcodes an <h2> for the month title and an <h6> above each day's events, wherever the calendar sits on
// the page. The Events Calendar block records the level the editor chose (data-heading-level); aria-level
// restates it for assistive tech, and is redone after each month change as MEC redraws the headings.
const setHeadingLevels = (wrap) => {
  const level = Number(wrap.closest('[data-heading-level]')?.dataset.headingLevel);
  if (!level) return;

  wrap.querySelectorAll('.mec-calendar-header h2').forEach((heading) => heading.setAttribute('aria-level', level));
  wrap.querySelectorAll('.mec-table-side-title').forEach((heading) => heading.setAttribute('aria-level', Math.min(level + 1, 6)));
};

// settleMonth clicks a day, which only does anything once MEC (jQuery, on ready) has bound its handlers.
let mecReady = document.readyState === 'complete';
window.addEventListener('load', () => {
  // MEC wires its calendars up after load too, so give it a beat before clicking anything.
  setTimeout(() => {
    mecReady = true;
    document.dispatchEvent(new Event('mec-calendar-ready'));
  }, 600);
});

document.querySelectorAll('.mec-wrap').forEach((wrap) => {
  let queued = false;
  const run = () => {
    queued = false;
    wrap.querySelectorAll('.mec-calendar').forEach((calendar) => {
      paintDots(calendar);
      if (mecReady) settleMonth(calendar);
    });
    enhanceTabs(wrap);
    setHeadingLevels(wrap);
  };

  new MutationObserver(() => {
    if (queued) return;
    queued = true;
    requestAnimationFrame(run);
  }).observe(wrap, { childList: true, subtree: true, attributes: true, attributeFilter: ['class'] }); // class: the selected view tab

  document.addEventListener('mec-calendar-ready', run);
  run();
});
