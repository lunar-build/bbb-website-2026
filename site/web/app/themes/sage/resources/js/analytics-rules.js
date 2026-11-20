// Which links send which GA4 event. External links match on `host + path` prefix, links to this site on
// the path alone (so it works on local, staging and production). First match wins. Anything
// unmatched falls back to the generic detection in analytics.js (mailto, documents, external links).
// Event and parameter names are registered in GA4 (custom dimensions), so keep them in sync.
const LINK_RULES = [
  { match: 'cycleplanner.betterbybike.info/route-planning', event: 'cta_click', parameters: { cta_label: 'plan_route', destination: 'cycle_planner' } },
  { match: '/schemes-and-initiatives/loan-a-bike-scheme', event: 'cta_click', parameters: { cta_label: 'need_a_bike_find_out_more' } },
  { match: '/cycling-centres/bristol-cycling-centre/whats-on', event: 'cta_click', parameters: { cta_label: 'bristol_cycling_centre_whats_on' } },

  { match: 'travelwest.info/businesses/bristol', event: 'scheme_link_click', parameters: { scheme: 'bikes_for_business', district: 'bristol' } },
  { match: 'travelwest.info/businesses/north-somerset', event: 'scheme_link_click', parameters: { scheme: 'bikes_for_business', district: 'ns' } },
  { match: 'travelwest.info/businesses/south-gloucestershire', event: 'scheme_link_click', parameters: { scheme: 'bikes_for_business', district: 'sg' } },
  // The current Travelwest page is shared by every district, so the Link Card's title says which one.
  { match: 'travelwest.info/for-businesses', event: 'scheme_link_click', parameters: { scheme: 'bikes_for_business' }, districtFromCard: true, districtAliases: { north_somerset: 'ns', south_gloucestershire: 'sg' } },
  // Every district books through a different form link: the district is the Link Card's title (see analytics.js).
  { match: 'forms.office.com/pages/responsepage', event: 'scheme_link_click', parameters: { scheme: 'adult_training' }, districtFromCard: true },
  { match: 'forms.office.com/e/fgm0saEqbJ', event: 'scheme_link_click', parameters: { scheme: 'borrow_a_bike' }, districtFromCard: true },
  { match: '/training/schools-bikeabiliy-training/school-bikeability-booking-enquiry', event: 'scheme_link_click', parameters: { scheme: 'schools_bikeability', district: 'bristol' } },
  { match: 'n-somerset.gov.uk/my-services/parking-travel-roads/transport-travel/cycling/cycling-training', event: 'scheme_link_click', parameters: { scheme: 'schools_bikeability', district: 'nsc' } },
  { match: 'southglos.gov.uk/transport-and-streets/transport/road-safety-transport/road-safety/cycle-safety/bikeability-in-schools', event: 'scheme_link_click', parameters: { scheme: 'schools_bikeability', district: 's_gloucestershire' } },

  { match: 'forms.office.com/e/WRXKS5YaLX', event: 'form_link_click', parameters: { form_name: 'order_map_feedback' } },
  { match: 'forms.office.com/e/ETgQ2qcu7A', event: 'form_link_click', parameters: { form_name: 'bristol_cycling_centre_interest' } },
];

// Documents by file name (without extension, lower case) → file_label, as named in the legacy GA4 events.
const GUIDE_FILES = {
  'getcycling_bcc-nov2014-lores': 'get_cycling',
  'bike-bath-brochure-aw': 'cycle_in_bath',
  '10-cycling-routes-in-bristol-june-2018_web': 'cycling_bristol',
  '25214-better-by-bike-clevedon-g': 'clevedon_film_tv_locations',
  '25214-better-by-bike-portishead-f': 'portishead_public_art_path',
  'south-glos-leisure-rides-2015': 'leisure_rides_s_glos',
  'longer-bike-rides-ebook': 'longer_rides_s_glos',
  'bd10784-mountain-bike-trails-leaflet-web': 'mtb_trails_ashtoncourt_leighwoods',
};

const REGIONAL_MAP_FILES = {
  'bath': 'bath',
  'bath-inset': 'bath_city_centre',
  'banes': 'bath_regional_cycle',
  'midsomer-norton-and-radstock': 'midsomer_norton_westfield_radstock',
  'keynsham-and-saltford': 'keynsham_saltford',
  'bristol-21k': 'bristol_regional',
  'bristol-8k': 'bristol_city_centre',
  'north-somerset': 'n_somerset_regional',
  'clevedon': 'clevedon',
  'nailsea': 'nailsea',
  'portishead': 'portishead',
  'weston-super-mare': 'wsm',
  'strawberry-line-map-for-new-leaflet': 'strawberry_line',
  'pier-to-pier-way-route-map': 'pier_to_pier',
  'south-glos': 's_glos_regional',
  'bradley-stoke-patchway': 'bradley_stoke_pathway',
  'chipping-sodbury-yate': 'chipping_sodnury_yate',
  'frampton-cotterell-and-winterbourne': 'frampton_cotterell_winterbourne',
  'thornbury-alveston': 'thornbury_alveston',
};

// Partner sites by `host + path` prefix, for outbound_click's `partner` (otherwise the domain's first label).
const PARTNERS = [
  { match: 'twitter.com/hashtag/ibikeitilikeit', partner: 'ibikeitilikeit' },
  { match: 'bcc.maps.arcgis.com', partner: 'bcc_hangar_map' },
  { match: 'allcyclebathandwest.com', partner: 'cycling_centres_all_cycle_bath' },
  { match: 'strawberrycycles.com', partner: 'cycling_centres_strawberry_lines_cycles' },
  { match: 'milestonestrust.org.uk/warmley-wheelers', partner: 'cycling_centres_warmley_wheelers' },
  { match: 'fixmystreet.com', partner: 'community_support_fix_my_street' },
];

// Email addresses with a set contact_label (otherwise the domain).
const EMAIL_LABELS = { 'warmleywheelers@milestonestrust.org.uk': 'warmley_wheelers' };

// Gravity Forms by slugified title, where the legacy form_name differs.
const FORM_NAMES = { public_bike_pumps_report_a_fault: 'report_pump_fault' };

export const slugify = (text) => text.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');

/** { file_category, file_label } for a document's file name (lower case, no extension). */
export function fileDetails(fileName) {
  if (fileName in GUIDE_FILES) {
    return { file_category: 'guide', file_label: GUIDE_FILES[fileName] };
  }

  if (fileName in REGIONAL_MAP_FILES) {
    return { file_category: 'regional_map', file_label: REGIONAL_MAP_FILES[fileName] };
  }

  return { file_category: 'document', file_label: slugify(fileName) };
}

export function partnerFor(url) {
  const target = `${url.hostname.replace(/^www\./, '')}${url.pathname}`.toLowerCase();

  return PARTNERS.find(({ match }) => target.startsWith(match))?.partner ?? slugify(url.hostname.replace(/^www\./, '').split('.')[0]);
}

export const emailLabel = (address) => EMAIL_LABELS[address.toLowerCase()] ?? slugify(address.split('@')[1] || address);

export const formName = (title, formId) => (title ? FORM_NAMES[slugify(title)] ?? slugify(title) : `gravity_form_${formId}`);

/** The rule for a link, or null. The protocol and a leading `www.` are ignored. */
export function findLinkRule(url, siteHostname = location.hostname) {
  const path = url.pathname.replace(/\/$/, '');
  const target = url.hostname === siteHostname ? path : `${url.hostname.replace(/^www\./, '')}${path}`;

  return LINK_RULES.find(({ match }) => target.toLowerCase().startsWith(match.toLowerCase())) ?? null;
}
