// Powers the "Plan a route" variant's From/To inputs: Google Places
// Autocomplete captures a real lat/lng per field, then submit builds the
// `locations` query param the external cycleplanner.betterbybike.info app
// expects — `?locations=` + JSON.stringify([{lat, lng, name, type}, ...]),
// lat/lng as strings rounded to 5dp. The "Find nearby feature" variant has
// no JS wiring yet (see the TODO in journey-planner-widget.blade.php).
//
// window.initJourneyPlannerPlaces is the `callback=` the Google Maps script
// tag calls once loaded (see JourneyPlannerWidget::assets()) — the script
// only loads at all when an API key is set and this block is on the page.

// TODO: Implement JS wiring for the "Find nearby feature" variant. Also hook up google Places Autocomplete as currently not finished.

window.initJourneyPlannerPlaces = () => {
  document
    .querySelectorAll(
      '.c-journey-planner-widget__form[data-variant="plan_route"]',
    )
    .forEach((form) => {
      const fromInput = form.querySelector('[name="from"]');
      const toInput = form.querySelector('[name="to"]');

      [fromInput, toInput].forEach((input) => {
        if (!input) return;

        let autocomplete;

        try {
          autocomplete = new google.maps.places.Autocomplete(input, {
            fields: ['geometry', 'formatted_address'],
          });
        } catch (error) {
          console.error(
            'Google Places Autocomplete failed to initialise:',
            error,
          );
          return;
        }

        autocomplete.addListener('place_changed', () => {
          const place = autocomplete.getPlace();
          const location = place.geometry?.location;

          if (!location) {
            delete input.dataset.lat;
            delete input.dataset.lng;
            return;
          }

          input.dataset.lat = location.lat();
          input.dataset.lng = location.lng();
          input.dataset.name = place.formatted_address ?? input.value;
        });
      });

      form.addEventListener('submit', (event) => {
        event.preventDefault();

        const missing = [fromInput, toInput].find(
          (input) => input && !input.dataset.lat,
        );

        if (missing) {
          missing.focus();
          return;
        }

        const locations = [
          { ...pointFrom(fromInput), type: 'from' },
          { ...pointFrom(toInput), type: 'to' },
        ];

        window.location.href = `${form.dataset.redirectUrl}?locations=${encodeURIComponent(JSON.stringify(locations))}`;
      });
    });
};

function pointFrom(input) {
  return {
    lat: Number(input.dataset.lat).toFixed(5),
    lng: Number(input.dataset.lng).toFixed(5),
    name: input.dataset.name,
  };
}
