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
| `core/list` | Bullet List. Bold, italic and links are kept; nested sub-items follow their parent (one level only) |
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

- Settings → Permalinks → Custom Structure: `/news/%postname%/`. This matches
  the old site's URLs and the `/news/...` links inside post content.
- Clear the ACF Composer cache if blocks look stale: `wp acorn acf:clear`.
- Drop the scratch database and delete the uploaded files:

```bash
sudo mysql -u root -p -e "DROP DATABASE prod_scratch;"
rm /tmp/old-site.sql /tmp/uploads.tar.gz
```

To roll back, run `wp db import /tmp/before-news-migration.sql`. The extracted
uploads don't need removing.
