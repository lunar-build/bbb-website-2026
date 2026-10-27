// "Plan a route" From/To inputs: Places Autocomplete captures lat/lng per field,
// submit builds `?locations=` + JSON.stringify([{lat, lng, name, type}]) (lat/lng as
// 5dp strings) for the external cycleplanner.betterbybike.info app.
//
// initJourneyPlannerPlaces is the Google Maps script's `callback=` (see
// JourneyPlannerWidget::assets(), which only loads it when an API key is set).

// TODO: wire up the "Find nearby feature" variant (Places autocomplete on "Your location" + redirect
// using the selected `nearby_feature` radio value). Blocked on the cycle planner repo supporting a
// "what's near you" search; until then the variant is display-only (see the submit no-op below).

// TODO: check a Google Maps API key is set (Theme Options) — without one none of this runs.

window.initJourneyPlannerPlaces = () => {
  document
    .querySelectorAll(
      '.c-journey-planner-widget__form[data-variant="plan_route"]',
    )
    .forEach((form) => {
      // Places needs the native <input>, exposed as wa-input's `.input` (the host
      // element isn't one).
      const fromInput = form.querySelector('wa-input[name="from"]')?.input;
      const toInput = form.querySelector('wa-input[name="to"]')?.input;

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
          setFieldError(
            form,
            input,
            'Location search is unavailable right now.',
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
          clearFieldError(form, input);
        });
      });

      form.addEventListener('submit', (event) => {
        event.preventDefault();

        const missing = [fromInput, toInput].find(
          (input) => input && !input.dataset.lat,
        );

        if (missing) {
          setFieldError(
            form,
            missing,
            'Select a location from the list of suggestions.',
          );
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

// Runtime version of <x-input>'s $error prop (components/input.blade.php).
function setFieldError(form, input, message) {
  const errorEl = form.querySelector(`#${input.name}-error`);
  if (!errorEl) return;

  errorEl.textContent = message;
  errorEl.hidden = false;
  input.setAttribute('aria-invalid', 'true');
  input.setAttribute('aria-describedby', errorEl.id);
}

function clearFieldError(form, input) {
  const errorEl = form.querySelector(`#${input.name}-error`);
  if (errorEl) errorEl.hidden = true;
  input.removeAttribute('aria-invalid');
  input.removeAttribute('aria-describedby');
}

function pointFrom(input) {
  return {
    lat: Number(input.dataset.lat).toFixed(5),
    lng: Number(input.dataset.lng).toFixed(5),
    name: input.dataset.name,
  };
}

// Find nearby is display-only until the cycle planner supports it (see TODO above).
document
  .querySelectorAll('.c-journey-planner-widget__form[data-variant="find_nearby"]')
  .forEach((form) => form.addEventListener('submit', (event) => event.preventDefault()));
