// Arrows move [data-active] between days; CSS shows only that day below md.
document.querySelectorAll('[data-events-timetable]').forEach((period) => {
  const days = [...period.querySelectorAll('[data-day]')];
  const current = period.querySelector('[data-events-timetable-current]');
  if (!days.length || !current) return;

  let index = Math.max(0, days.findIndex((day) => day.hasAttribute('data-active')));

  const show = (next) => {
    index = (next + days.length) % days.length;
    days.forEach((day, i) => day.toggleAttribute('data-active', i === index));
    current.textContent = days[index].dataset.day;
  };

  period.querySelectorAll('[data-events-timetable-step]').forEach((button) => {
    button.addEventListener('click', () => show(index + Number(button.dataset.eventsTimetableStep)));
  });
});
