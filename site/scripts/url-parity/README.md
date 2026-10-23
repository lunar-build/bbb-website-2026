# URL parity

Tools for keeping the live betterbybike.info URLs working on the new site.

## `check.php`

Requests every URL in the live sitemaps on another site and reports ok / redirect / missing.

```bash
php scripts/url-parity/check.php                           # against https://betterbybike.test
php scripts/url-parity/check.php --base=https://staging…   # another environment
php scripts/url-parity/check.php --only=post,category      # some sitemaps only
```

Read-only. Exits 1 if any URL is missing.

## `redirection-import.json`

The live site's Redirection plugin rules (exported 2026-10-23), cleaned up for the new site:

- 31 → 33 rules: three added (below), and the duplicate `/brean-down-way` rule is dropped.
- Internal targets are relative paths (`/schemes-and-initiatives/…`), not `https://betterbybike.info/…`, so they work on every environment.
- The two rules that pointed at `/cycling-centres/bristol-family-cycling-centre/` (itself redirected) now go straight to `/cycling-centres/bristol-cycling-centre/`.
- `bikecheck` gained its missing leading slash.
- Form confirmations are shown on the form's own page (Gravity Forms), so the old confirmation pages are redirected instead of kept: `/thank-you/` → `/`, `/bristol-cycling-centre-survey/` → `/cycling-centres/bristol-cycling-centre/`, and `/loan-bike-scheme-end-of-survey` (which went to `/thank-you/`) → `/schemes-and-initiatives/borrow-a-bike-scheme/`. The test page `/types-of-bike-test/` → `/find-the-right-bike/types-of-bike/`. A redirect runs before the page, so the old pages can be trashed on each environment once these are imported.
- `/community-support/free-bike-checks` now goes to `/schemes-and-initiatives/free-bike-checks/` (production sent it to the borrow-a-bike page, which looked like a mistake).

Import on each environment (Redirection active, tables installed):

```bash
wp redirection database install      # first time only
wp redirection import scripts/url-parity/redirection-import.json --group=1
```

Check it afterwards by requesting each source path: it should 301 to the target. Import once per environment: running it again adds duplicates unless `--duplicate-mode` is set.
