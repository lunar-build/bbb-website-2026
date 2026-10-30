# News migration

Copies the old site's published news posts and its whole media library into
this site. The old theme's blocks are converted into this theme's blocks on the
way in. Post and attachment IDs are kept, so featured images, image fields and
`wp-image-123` classes all keep working.

`migrate-news.php` reads the old site from a **separate scratch database**. It
never touches the old site itself, and it only writes to this site's database.

## Block mapping

| Old block | Becomes |
|---|---|
| `custom/lead` | Standfirst |
| `acf/quote` (content/author/source/image) | Quote (quote/attribution name/role/image). Quotes over 200 characters use the standard size |
| `acf/notification` | Callout Text. Any link is added as an inline `<a>` |
| `acf/download-link` | Link Card (download). Consecutive ones merge into a single block |
| `acf/bike-card` | One Timeline plus one Media Gallery of the cards' images |
| `core/image` | Image block. A `figcaption` becomes the caption |
| `core/buttons` | One Button block per button (Yellow, Large, Left) |
| `core/table` | Table (`acf/data-table`). A first row that's all bold becomes the column headings |
| `core/columns` with one image per column | Two Column Content with an Image layout on each side |
| Group/columns holding only `acf/card` blocks | Card Grid with "link" cards (icon or image, title, text, link) |
| `core/list` | Bullet List with no top padding and Small bottom padding (the block's own default is none). Numbered lists stay numbered, nested lists keep their depth (up to 3 levels, via each item's Indent), and bold, italic and links are kept |
| `core/block` (reusable) | The reusable block's content, inlined and converted |
| `acf/faq` | Accordion (one item per question; answers keep their links) |
| `acf/transcript` | Accordion with a single "Transcript" item |
| `acf/step-by-step` | Timeline with numbered markers (step title = heading, step content = body) |
| `acf/staggered-content-cards` | Testimonial Card Carousel (image, author, subtitle, quote). A blue background becomes Blue light, and a title becomes an H2 above it |
| `acf/calendar` | One Events Timetable per page, which shows the term timetables. The "… Timetable" headings above them go |
| `acf/divider` | Separator |
| `acf/illustration`, `acf/illustration-divider` | Illustration block with the same character, up to three objects and the "remove blue background" setting. "Instructor and girl" has no image on the live site, so that block is dropped |
| `acf/card` layouts where every card is an icon card with a link | Link Card, with the icon as the logo. The old site showed these icons small; a Card Grid image would be large |
| `acf/card` layouts linking to an uploaded file (map, guide) | Link Card with download cards (file, label from the old link text, size). Adjacent layouts under one heading merge into one block. Regional cycle maps (701) turns every layout into Link Cards except the photo cards (title and header photo on every card), which stay a Card Grid |
| `acf/card` outside a card layout | Card Grid with "link" cards; consecutive cards share one grid. A card sourced from a post takes its title, excerpt, image and a `?p=` link |
| Google map iframe (`core/html`, or loose HTML with no block) or Google `core/embed` | Map Embed. Drive "open?id=" links become My Maps embed URLs, and the title comes from the iframe or the heading above |
| Hard-wrapped text (a newline every few words, from Word) | Lines joined in card text, callouts, accordions, timelines and quotes, and mid-sentence line breaks in paragraphs. Blank lines stay paragraph breaks and address line breaks stay |
| Columns with no images | Unwrapped: the blocks keep their order but lose the side-by-side layout |
| Heading `h3`/`h4` class, centred text | Class and alignment dropped; the heading level stays |
| `acf/form` / Contact Form 7 selector | Form block with the form rebuilt in Gravity Forms (see below) |
| Landing pages (Homepage 220, Why cycle 241) | Video Hero with the planner widget; Row CTA Banner for the blue strip and for Why cycle's Instagram hashtag banner; carousels become Rolling Stats Carousels (a slide's link becomes its button); "plan a bike ride" is Two Column Content with the cycle planner picture (found by file name); "Explore ways to get a bike", "I bike it, I like it!" and the raw-HTML "Healthier, faster…" line are CTA Banners; on Why cycle every heading is a CTA Banner; quote cards are a Case Study Grid of the latest four; rides are a route Card Row; events an Icon Link Grid; cycling centres a Card Grid then a CTA Banner; latest news a news Card Row; the page's own illustration settings become a closing Illustration; the "News letter" Gravity Form closes the page. The Instagram feed becomes an Instagram Feed block (default feed, its fixed "Love cycling?" heading); the heading above it is dropped |
| `core/group` | Unwrapped: the old theme's group styles mean nothing here, so its blocks are lifted out and converted like any other |
| `core/embed`, paragraphs, headings | Unchanged (VideoBlock only takes uploaded files, not YouTube) |

Conversions apply at every nesting level, not just the top. Content should
always use the theme's own blocks (`app/Blocks`) where one fits: `check`
lists every old-theme block and every core block left after the transform,
at any depth. Anything there other than paragraphs, headings and embeds
needs a conversion added.

Any ACF block that comes straight after a heading has its top padding removed,
so it sits under that heading. The `check` mode lists anything left unmapped.

## Modes

Run from the Bedrock root (`/site` locally, `/srv/www/<site>/current` on a server):

```bash
wp eval-file scripts/news-migration/migrate-news.php check         # read-only report
wp eval-file scripts/news-migration/migrate-news.php preview       # read-only: writes ./migration-preview/*.html
wp eval-file scripts/news-migration/migrate-news.php categories    # create missing categories (by slug, keeps hierarchy)
wp eval-file scripts/news-migration/migrate-news.php attachments   # copy all attachments (skips ones already copied)
wp eval-file scripts/news-migration/migrate-news.php posts [1288,7070]  # copy posts; re-running replaces earlier imports
wp eval-file scripts/news-migration/migrate-news.php case-studies [1019] # copy case studies (run after attachments)
wp eval-file scripts/news-migration/migrate-news.php map-pages          # copy shops/BMX/pumps pages (run after attachments)
wp eval-file scripts/news-migration/migrate-news.php pages [300,701]    # all other pages -> hero + converted content (run after attachments)
wp eval-file scripts/news-migration/migrate-news.php cycling-centre [753] # Cycling Centre sessions -> MEC events (run after attachments)
wp eval-file scripts/news-migration/migrate-news.php events [7065,7040,7042] # one-off events -> MEC events (run after cycling-centre)
```

These environment variables can be set in front of the command, e.g. `NEWS_SOURCE_DB=old_site wp eval-file ...`:

| Variable | Default |
|---|---|
| `NEWS_SOURCE_DB` | `prod_scratch` |
| `NEWS_SOURCE_DB_USER` / `NEWS_SOURCE_DB_PASSWORD` | this site's DB credentials |
| `NEWS_SOURCE_PREFIX` | `wp_` |
| `NEWS_SOURCE_HOME` | `https://betterbybike.info` (rewritten to this site's URL) |

`case-studies` copies the old `case_study` posts with their IDs, featured
image, `quote` and `card_image` fields and `case_study_tag` terms. The old
illustration fields are dropped. The quote also becomes the post excerpt, which
the archive cards show, because the old site had no excerpts.

`map-pages` copies the old site's seven shops, BMX and pumps pages, plus their
three parent pages, keeping IDs and URLs. Each one moves onto the Map Listing
template with its dataset and preset services, which were decoded from the old
`selected_categories` field. The featured image becomes an Image Hero block.
Pages without one (BMX) get a Text Hero instead. The parent pages are created
empty, because their old Child Page Cards block has no equivalent yet.

It also rebuilds the old Contact Form 7 "Public Bike Pumps - Report a fault"
form in Gravity Forms (same fields, emailed to betterbybike@bristol.gov.uk,
reused by title on re-runs) and copies its page,
`/shops-and-repair/public-bike-pumps/report-a-bike-pump-fault/`, with a Text
Hero and a Form block. Re-runs replace the pages in place, so nav menu items
linking to them survive.

`pages` copies every other published page, keeping IDs, parents, menu order and
URLs. Each gets a hero in front (an Image Hero from the featured image, else a
Text Hero) and its content converted like a post's. Every page goes on the Sticky Nav
template, as on the live site; its menu is built from the H2s, and pages without
any just have no menu. `check` prints each page's H2 count. Reusable blocks (`core/block`) are inlined so their
content is converted too. Re-runs replace earlier imports in place, so nav menu
items linking to the pages survive.

Left out: the pages other modes own (the map pages, the Cycling Centre pages and
the pump fault page), the News archive (59), the Contact Form 7 leftovers
(1550 Thank you, 1717 survey, 1985 test) and the two landing pages (220
Homepage, 241 Why cycle), which wait for their own conversions. Blocks without a
conversion yet still show in `check` under "Old-theme blocks left".

**Forms.** The old site's Contact Form 7 forms are rebuilt as Gravity Forms and the Form block is put
where the old form was (centred, no heading: the page has its own). Fields, required flags, the
submit button text, the mail recipients, sender, subject, body and the confirmation message come from
the old form; CF7 tags become Gravity Forms merge tags and the first email field becomes the
reply-to. Forms are matched by title, so a re-run updates them in place (and keeps their IDs). Only
the forms pages actually use are converted (`NEWS_CF7_FORMS`): Bristol Cycling Centre Contact,
Register Event, Contact Page, Volunteering and School Bikeability Enquiry, plus the pump fault form
built by `map-pages`. The old forms nothing uses (newsletter, adult cycle training, order a map, cycle
map feedback, bike hangars, and the schools form 2420 which has duplicate field names) are left out.
"School Bikeability Enquiry" is old form 2429, labelled TEST but the one the page uses. `check` and
`preview` never create forms.

`cycling-centre` copies the old Bristol Cycling Centre sessions
(`cycling_centre_event`) into Modern Events Calendar as `mec-events`, keeping
IDs, slugs, featured images and Yoast meta. MEC must be active.

The classes are imported **undated**. The old site's timetable (the
`options_schedule` ACF options repeater, one row per term) becomes **term
timetables** instead: every term that hasn't ended is created as a published
`bbb_event_timetable` with its weekly slots, and publishing it writes the dated
sessions into each event (see the root README, "Event timetables"). Slots that
fail validation are skipped and reported. One example is Term 1's Wednesday
Disability Cycling at 11:45–00:30; fix it in the term editor. The timetable
fillers (Break, Closed, No sessions) are skipped, and so are the old site's
one-off `event` posts (use the `events` mode for those). Re-running deletes and
recreates the migrated terms.

Each class gets the Bristol Cycling Centre category, which keeps its live
`/cycling-centres/bristol-cycling-centre/whats-on/{slug}/` URL (see
`app/filters.php`). The old "upcoming sessions" block is dropped, because the
event template shows the next session. So is the old CF7 contact form on
Schools and Private Hire, which still needs rebuilding in Gravity Forms. What's
on's per-term calendar blocks become a single Events Timetable that shows the
event timetables automatically.

`events` copies the old site's one-off `event` posts into MEC, keeping IDs,
slugs (`/events/{slug}/`, as on the live site), featured images and Yoast
meta. The old `start_date`/`end_date`/`end_time` fields become the MEC date,
and a midnight start with no end time becomes "all day". `cost` becomes MEC's
Cost (a plain "Free" becomes `0`, the same as ticking MEC's "This event is
free", in both this and the `cycling-centre` mode), and `event_category` terms become `mec_category` terms with the same
slugs. `location` becomes an MEC location, named by the part before the first
comma, so "Bristol Cycling Centre, Old Whitchurch…" reuses the existing
Bristol Cycling Centre location. With no ids it imports every event, newest
first. Finished events still show their date ("Date … This event has
finished"). Sessions that `cycling-centre` imports without timetable slots
are flagged `_event_undated`, so their placeholder date is never shown.

`pages` skips a page that has been edited since it was imported: it stores a hash of the imported
content, and a re-run leaves any page whose content no longer matches (and says so). Set
`NEWS_FORCE=1` in front of the command to overwrite it anyway. Pages imported before this was added
have no hash, so their first re-run replaces them.

Before a write mode copies rows under the old site's IDs, it deletes any local revision, auto-draft
or trashed row sitting on those IDs (they're never content). Anything else on a clashing ID makes the
mode stop and list it, so nothing real is overwritten by accident. Rows this script imported itself
are replaced in place on a re-run. To clear the rest too (a throwaway local database, say), put
`NEWS_REPLACE_CLASHES=1` in front of the command: each deleted row is listed as it goes. Don't use it
on staging or production unless the clashing rows really are disposable.

Imported posts are flagged with `_news_migrated` post meta. A re-run of `posts`
deletes and re-imports only flagged posts. If any other post, page, menu item
or revision already uses one of the old site's IDs, `posts` and `attachments`
refuse to run. `check` lists those rows so you can delete or renumber them first.

## Running it on a Trellis server (staging, then production)

You'll need:

- A fresh dump of the old site's database (`old-site.sql`).
- A tarball of the old site's `uploads` folder (`uploads.tar.gz`).
- This branch deployed, so `scripts/news-migration/` and the theme blocks it targets are on the server.

### 1. Upload the dump and the uploads

```bash
scp old-site.sql uploads.tar.gz web@<server>:/tmp/
```

### 2. Load the dump into a scratch database

Trellis's MySQL root password is `vault_mysql_root_password` in
`group_vars/<env>/vault.yml`. The site's own DB user and password are in that
same vault (`vault_wordpress_sites.<site>.env.db_password`) and `wordpress_sites.yml`.

```bash
ssh admin@<server>
sudo mysql -u root -p -e "CREATE DATABASE prod_scratch; GRANT ALL ON prod_scratch.* TO '<site db user>'@'localhost';"
sudo mysql -u root -p prod_scratch < /tmp/old-site.sql
```

The dump has no `USE` statement, so it loads only into `prod_scratch`.

### 3. Put the uploads in place

Trellis keeps uploads in `shared/uploads`, outside each release. Extract them
there so the files sit at `shared/uploads/2024/01/...`:

```bash
ssh web@<server>
cd /srv/www/<site>/shared
tar -xzf /tmp/uploads.tar.gz --strip-components=1 -C uploads   # match the tarball's top folder
ls uploads | head    # expect year folders: 2017 2018 ...
```

Check the tarball's top-level layout first with `tar -tzf /tmp/uploads.tar.gz | head`,
and adjust `--strip-components` to suit.

### 4. Back up, check, then import

```bash
cd /srv/www/<site>/current
wp db export /tmp/before-news-migration.sql

wp eval-file scripts/news-migration/migrate-news.php check
```

Resolve anything `check` reports: ID collisions, missing categories,
unknown ACF keys. Then run:

```bash
wp eval-file scripts/news-migration/migrate-news.php categories
wp eval-file scripts/news-migration/migrate-news.php attachments
wp eval-file scripts/news-migration/migrate-news.php posts 1288,7070   # spot-check these first
wp eval-file scripts/news-migration/migrate-news.php posts             # then everything
```

Run `attachments` before `posts`. The Link Card file-size labels are worked out
from the files on disk, and so are the image sizes.

### 5. Settings and tidy-up

- Settings → Permalinks → **Post name**. The theme (`app/filters.php`) then puts posts at `/news/{slug}/`
  like the old site, so the `/news/...` links inside post content work. Flush after deploy:
  `wp rewrite flush`.
- Clear the ACF Composer cache if blocks look stale: `wp acorn acf:clear`.
- Drop the scratch database and delete the uploaded files:

```bash
sudo mysql -u root -p -e "DROP DATABASE prod_scratch;"
rm /tmp/old-site.sql /tmp/uploads.tar.gz
```

To roll back, run `wp db import /tmp/before-news-migration.sql`. The extracted
uploads don't need removing.
