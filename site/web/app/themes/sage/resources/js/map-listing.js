// Map for the Map Listing template (template-map-listing.blade.php). Pins come
// from the results fragment's [data-map-markers] JSON, re-read whenever
// archive-filters.js swaps the results, so the map always matches the cards.
const container = document.querySelector('[data-map-listing]');

// Figma "Pin Blue" map pin.
const PIN = `data:image/svg+xml,${encodeURIComponent(
  '<svg xmlns="http://www.w3.org/2000/svg" width="28" height="38" viewBox="0 0 28 38"><path d="M14 0C6.3 0 0 6.2 0 13.9 0 24.3 14 38 14 38s14-13.7 14-24.1C28 6.2 21.7 0 14 0z" fill="#53A6DC"/><circle cx="14" cy="14" r="5.5" fill="#fff"/></svg>',
)}`;

// Bristol, Bath and the West of England — used when there are no pins.
const DEFAULT_CENTER = { lat: 51.43, lng: -2.6 };

const loadGoogleMaps = (key) =>
  new Promise((resolve, reject) => {
    if (window.google?.maps?.Map) return resolve(window.google.maps);

    window.initMapListing = () => resolve(window.google.maps);
    const script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(key)}&loading=async&callback=initMapListing`;
    script.async = true;
    script.onerror = reject;
    document.head.append(script);
  });

const escapeHtml = (text) =>
  String(text).replace(/[&<>"']/g, (char) => `&#${char.charCodeAt(0)};`);

if (container) {
  // Google's global hook for a rejected key (e.g. this domain isn't in its
  // allowed referrers) — drop the map rather than show Google's error box.
  window.gm_authFailure = () => container.remove();

  loadGoogleMaps(container.dataset.apiKey)
    .then((maps) => {
      const map = new maps.Map(container, {
        center: DEFAULT_CENTER,
        zoom: 9,
        mapId: container.dataset.mapId || undefined,
        streetViewControl: false,
      });
      const info = new maps.InfoWindow();
      let markers = [];

      const render = () => {
        const json = document.querySelector('[data-map-markers]')?.textContent || '[]';
        const bounds = new maps.LatLngBounds();

        markers.forEach((marker) => marker.setMap(null));
        info.close();

        markers = JSON.parse(json).map((item) => {
          const marker = new maps.Marker({
            map,
            position: { lat: item.lat, lng: item.lng },
            title: item.name,
            icon: { url: PIN, scaledSize: new maps.Size(28, 38) },
          });

          marker.addListener('click', () => {
            info.setContent(
              `<div class="c-map-listing__info"><strong>${escapeHtml(item.name)}</strong>` +
                (item.address ? `<br>${escapeHtml(item.address)}` : '') +
                `<br><a href="#${item.id}">View details</a></div>`,
            );
            info.open({ map, anchor: marker });
          });

          bounds.extend(marker.getPosition());
          return marker;
        });

        if (markers.length > 1) map.fitBounds(bounds);
        else if (markers.length === 1) map.setCenter(markers[0].getPosition());
      };

      render();
      document.addEventListener('archive:updated', render);
    })
    .catch(() => container.remove());
}
