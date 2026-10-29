// Arrows move [data-active] between days; CSS shows only that day below md.
// Arrow keys also move between sessions: up/down within a day, left/right to the
// nearest start time on the neighbouring day. Tab still visits every link.
document.querySelectorAll('[data-events-timetable]').forEach((period) => {
  const days = [...period.querySelectorAll('[data-day]')];
  const current = period.querySelector('[data-events-timetable-current]');
  const currentDay = period.querySelector('[data-events-timetable-day]');
  const currentCount = period.querySelector('[data-events-timetable-count]');
  const steps = [...period.querySelectorAll('[data-events-timetable-step]')];
  if (!days.length || !current || !currentDay || !currentCount) return;

  let index = Math.max(0, days.findIndex((day) => day.hasAttribute('data-active')));

  const countLabel = (day) => {
    const count = day.querySelectorAll('.c-events-timetable__event').length;
    if (count === 0) return current.dataset.labelNone;
    if (count === 1) return current.dataset.labelOne;
    return current.dataset.labelMany.replace('%d', count);
  };

  // Stop at either end of the week, but keep the button focusable.
  const updateSteps = () => {
    steps.forEach((button) => {
      const target = index + Number(button.dataset.eventsTimetableStep);
      button.setAttribute('aria-disabled', String(target < 0 || target >= days.length));
    });
  };

  const show = (next) => {
    if (next < 0 || next >= days.length) return;
    index = next;
    days.forEach((day, i) => day.toggleAttribute('data-active', i === index));
    currentDay.textContent = days[index].dataset.day;
    currentCount.textContent = `, ${countLabel(days[index])}`;
    updateSteps();
  };

  steps.forEach((button) => {
    button.addEventListener('click', () => show(index + Number(button.dataset.eventsTimetableStep)));
  });

  updateSteps();

  const startOf = (link) => Number(link.closest('li').style.getPropertyValue('--start'));
  // Hidden days (display: none below md) have no layout box, so they drop out.
  const linksOf = (day) => [...day.querySelectorAll('a')].filter((link) => link.offsetParent !== null);

  period.addEventListener('keydown', (event) => {
    const link = event.target.closest?.('.c-events-timetable__link');
    if (!link || event.altKey || event.ctrlKey || event.metaKey || event.shiftKey) return;

    const day = link.closest('[data-day]');
    const links = linksOf(day);
    let target;

    if (event.key === 'ArrowDown') target = links[links.indexOf(link) + 1];
    else if (event.key === 'ArrowUp') target = links[links.indexOf(link) - 1];
    else if (event.key === 'ArrowRight' || event.key === 'ArrowLeft') {
      const direction = event.key === 'ArrowRight' ? 1 : -1;
      for (let i = days.indexOf(day) + direction; !target && days[i]; i += direction) {
        const candidates = linksOf(days[i]);
        target = candidates.sort((a, b) => Math.abs(startOf(a) - startOf(link)) - Math.abs(startOf(b) - startOf(link)))[0];
      }
    } else return;

    event.preventDefault();
    target?.focus();
  });
});
