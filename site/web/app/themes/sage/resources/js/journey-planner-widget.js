// "Plan a route" From/To inputs: Places Autocomplete captures lat/lng per field,
// submit builds `?locations=` + JSON.stringify([{lat, lng, name, type}]) (lat/lng as
// 5dp strings) for the external cycleplanner.betterbybike.info app.
//
// "Find nearby": one location field (Places Autocomplete, or the browser's geolocation via
// the trailing icon button) + a radio, redirecting to the planner's
// `/near-me?type=&lat=&lng=&name=` page.
//
// initJourneyPlannerPlaces is the Google Maps script's `callback=` (see
// JourneyPlannerWidget::assets(), which only loads it when an API key is set).

// TODO: check a Google Maps API key is set (Theme Options) — without one none of this runs.

const NEAR_ME_PATH = '/near-me';
const DEFAULT_PLANNER_ORIGIN = 'https://cycleplanner.betterbybike.info';

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

      [fromInput, toInput].forEach((input) => setUpAutocomplete(form, input));

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

  document
    .querySelectorAll(
      '.c-journey-planner-widget__form[data-variant="find_nearby"]',
    )
    .forEach(setUpNearby);
};

function setUpNearby(form) {
  const input = form.querySelector('wa-input[name="location"]')?.input;
  const locateButton = form.querySelector('.c-input__icon');
  const radios = form.querySelector('wa-radio-group');

  setUpAutocomplete(form, input);

  locateButton?.addEventListener('click', () => {
    if (!navigator.geolocation) {
      setFieldError(form, input, 'Your browser can’t share your location.');
      return;
    }

    navigator.geolocation.getCurrentPosition(
      ({ coords }) => {
        input.dataset.lat = coords.latitude;
        input.dataset.lng = coords.longitude;
        delete input.dataset.name;
        input.value = 'My current location';
        clearFieldError(form, input);
      },
      () =>
        setFieldError(
          form,
          input,
          'We couldn’t get your location. Type a postcode or address instead.',
        ),
    );
  });

  form.addEventListener('submit', (event) => {
    event.preventDefault();

    if (!input.dataset.lat) {
      setFieldError(
        form,
        input,
        'Select a location from the list of suggestions, or use your current location.',
      );
      input.focus();
      return;
    }

    const params = new URLSearchParams({
      type: radios.value,
      lat: Number(input.dataset.lat).toFixed(5),
      lng: Number(input.dataset.lng).toFixed(5),
    });

    if (input.dataset.name) params.set('name', input.dataset.name);

    window.location.href = `${nearMeBase(form.dataset.redirectUrl)}?${params}`;
  });
}

// The planner's near-me page lives at the root of whatever site the block's
// journey planner URL points at (local/staging/production).
function nearMeBase(plannerUrl) {
  try {
    return new URL(NEAR_ME_PATH, new URL(plannerUrl).origin).href;
  } catch {
    return new URL(NEAR_ME_PATH, DEFAULT_PLANNER_ORIGIN).href;
  }
}

function setUpAutocomplete(form, input) {
  if (!input) return;

  let autocomplete;

  try {
    autocomplete = new google.maps.places.Autocomplete(input, {
      fields: ['geometry', 'formatted_address'],
      componentRestrictions: { country: 'gb' },
    });
  } catch (error) {
    console.error('Google Places Autocomplete failed to initialise:', error);
    setFieldError(form, input, 'Location search is unavailable right now.');
    return;
  }

  autocomplete.addListener('place_changed', () => {
    const place = autocomplete.getPlace();
    const location = place.geometry?.location;

    if (!location) {
      clearPoint(input);
      return;
    }

    input.dataset.lat = location.lat();
    input.dataset.lng = location.lng();
    input.dataset.name = place.formatted_address ?? input.value;
    clearFieldError(form, input);
  });

  // Editing the text after picking a suggestion makes the stored point stale.
  input.addEventListener('input', () => clearPoint(input));

  // Enter picks the highlighted suggestion; without this it also submits the
  // form before `place_changed` has stored the coordinates (flashing the
  // "select a location" error). WA's wa-input submits on Enter unless the
  // event was defaultPrevented.
  input.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' && suggestionsOpen()) event.preventDefault();
  });
}

function suggestionsOpen() {
  return [...document.querySelectorAll('.pac-container')].some(
    (container) => container.offsetParent !== null,
  );
}

function clearPoint(input) {
  delete input.dataset.lat;
  delete input.dataset.lng;
  delete input.dataset.name;
}

// Runtime version of <x-input>'s $error prop (components/input.blade.php): the message goes
// in wa-input's `hint` (WA wires aria-describedby onto the native control, so it's read
// when focus returns to the field, and styles the error border) plus the sr-only
// role="alert" region so it's also announced straight away.
function setFieldError(form, input, message) {
  const host = input.getRootNode().host;
  const alertEl = form.querySelector(`#${input.name}-error`);

  host.hint = message;
  if (alertEl) alertEl.textContent = message;
}

function clearFieldError(form, input) {
  const host = input.getRootNode().host;
  const alertEl = form.querySelector(`#${input.name}-error`);

  host.hint = '';
  if (alertEl) alertEl.textContent = '';
}

function pointFrom(input) {
  return {
    lat: Number(input.dataset.lat).toFixed(5),
    lng: Number(input.dataset.lng).toFixed(5),
    name: input.dataset.name,
  };
}
