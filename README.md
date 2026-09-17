# bbb-website-2026

# Getting started

VSCode tasks will automatically run the following commands for you when you open the project. In your terminal tabs to the right, you will automatically see two SSH tunnels open in the `/site` and `/site/web/app/themes/sage` directories. You can use these terminals to run commands in the ddev environment e.g.:

```bash
# in /var/www/html/web/app/themes/sage
npm run dev
```

## Manual approach

1. Start the ddev environment
```bash
cd site && ddev start
```

2. Install dependencies
```bash
# in /site
ddev exec "composer install"
ddev exec "npm --prefix web/app/themes/sage install"
ddev exec "composer --working-dir web/app/themes/sage install"

```

3. Build the theme
```bash
# in /sit
ddev exec "npm --prefix web/app/themes/sage run build"
```

### ACF Pro & Gravity Forms — private Composer packages

Both plugins are premium/paid, so they aren't on Packagist or [WP Packages](https://wp-packages.org/) (the free-plugin mirror this project otherwise uses for `roots/wordpress` etc). Instead each has a **private mirror repo** in the `lunar-build` GitHub org that Composer pulls from directly:

| Plugin | Composer package | Source repo |
|---|---|---|
| ACF Pro | `wpengine/advanced-custom-fields` | `git@github.com:lunar-build/advanced-custom-fields-pro.git` |
| Gravity Forms | `gravityforms/gravityforms` | `git@github.com:lunar-build/gravityforms.git` |

Both are declared as `"type": "vcs"` entries in `site/composer.json`'s `repositories` array, alongside the `wp-packages` entry. To add another premium plugin later, follow the same pattern: get (or create) a `lunar-build/<plugin-slug>` mirror repo, add a matching `vcs` entry, `require` the package.

Install/update them the same way as any other Composer dependency:

```bash
ddev exec "composer install"                          # first install (uses composer.lock)
ddev composer update wpengine/advanced-custom-fields   # bump ACF Pro only
ddev composer update gravityforms/gravityforms         # bump Gravity Forms only
```

Both land in `web/app/plugins/` (gitignored — Composer-managed, don't hand-edit or commit).

#### Auth: GitHub token required

Since these are private repos, Composer needs a GitHub token with access to the `lunar-build` org to clone them. Set this up **once per machine**, not per-project — official steps: https://knowledge.lunar.build/#/ddev?id=persist-github-repositories-token-for-composer

1. Generate a **repo-scoped** token at https://github.com/settings/tokens
   - Use a **classic** token with the `repo` scope. Fine-grained tokens work too, but need every repo (`advanced-custom-fields-pro`, `gravityforms`, and any future ones) explicitly added to the token's repository access list — classic + `repo` scope is simpler and doesn't need updating each time a new private repo is added
   - If `lunar-build` enforces SSO, click "Enable SSO" / "Configure SSO" next to the token on that page and authorize it for the org — otherwise you'll get a `403 Write access to repository not granted` error even with a valid, correctly-scoped token
2. Drop it into DDEV's global Composer auth config so it applies across all DDEV projects on this machine:
   ```bash
   mkdir -p ~/.ddev/homeadditions/.composer
   cat > ~/.ddev/homeadditions/.composer/auth.json <<'EOF'
   {
     "github-oauth": {
       "github.com": "YOUR_TOKEN_HERE"
     }
   }
   EOF
   ddev restart
   ```
3. `ddev exec "composer install"` (or `ddev composer update ...`) should now clone both repos without prompting

If `composer install` fails with a 404 downloading either package, or silently fails without ever prompting for a token, delete `site/composer.lock` and re-run — this forces Composer to re-resolve and surface the actual auth error.

### First run / after installing WordPress

Once WP install screen has run (site loads, DB set up):

1. Grab `ACF_PRO_KEY` from 1Password and set it in `site/.env`
2. Activate ACF Pro and Gravity Forms plugins in WP admin (Plugins)
3. Confirm active theme is **Sage** (`web/app/themes/sage`) — WP admin > Appearance > Themes

# [ACF Composer](https://github.com/log1x/acf-composer)

Provides an artisan-like CLI for managing ACF fields and blocks in a WordPress project. Checkout the link above for example usage and documentation. See the below example.

## [TextHero Block](site/web/app/themes/sage/app/Blocks/TextHero.php)

Blade file: [site/web/app/themes/sage/resources/views/blocks/text-hero.blade.php](site/web/app/themes/sage/resources/views/blocks/text-hero.blade.php)

This block uses the core/heading and core/paragraph blocks as content. The block is registered in the `TextHero.php` file and the fields are defined in the `fields()` method. The block is rendered in the `with()` method, which passes the block data to the Blade template. The `template` property defines the default inner blocks for the block.

## Building a new block — use the `build-acf-block` skill

Repo ships a [Claude Code](https://claude.com/claude-code) skill at [.claude/skills/build-acf-block/SKILL.md](.claude/skills/build-acf-block/SKILL.md) that walks through the whole block-creation workflow so it's consistent across the team. Ask Claude Code to "create a block" (or invoke `/build-acf-block`) and it will:

1. Work out whether the block's UI needs plain Blade, or a Web Awesome component (`<wa-button>`, `<wa-card>`, etc.).
2. Scaffold the block with `wp acorn acf:block "Block Name"` (run this interactively, not piped through a script — see the skill file for why) and wire up `fields()`/`with()`/the Blade view following the existing blocks as reference.
3. Verify the block registers and renders, including checking real front-end output, not just the editor preview.

See `site/web/app/themes/sage/app/Blocks/FeatureCard.php` + `.../resources/views/blocks/feature-card.blade.php` for a worked example built via this workflow (Web Awesome `<wa-card>`/`<wa-button>`, no Lunar component needed for this one).

# Pattern library

Every block registered under `site/web/app/themes/sage/app/Blocks/` automatically
appears on a living style guide page at **`/pattern-library`** — each block shown with
its description and a rendered example, so a client can see what's available before
dropping a block into a page.

- **Logged-in only.** Anonymous visitors get a real 404, so it's safe to leave
  published without exposing placeholder content publicly.
- **No registration step.** The page is powered by
  [`App\View\Composers\PatternLibrary`](site/web/app/themes/sage/app/View/Composers/PatternLibrary.php),
  which discovers every block via ACF Composer's own registry
  (`app('AcfComposer')->composers()`) and re-renders each one standalone using its
  fixture data — the same mechanism already used for the block editor's empty-state
  preview. A new block just needs to follow the normal block-building conventions (see
  the `build-acf-block` skill's §6 "Pattern library"): a real `$description`, full
  `$example`/`example()` coverage for every ACF field (including media, via the
  bundled placeholder assets), and an `$exampleContent` string if it uses
  `InnerBlocks`. Get those right and the block appears correctly with nothing else to
  wire up.
- **Placeholder assets** live at
  `site/web/app/themes/sage/resources/images/placeholder/pattern-placeholder.svg` and
  `.../resources/videos/placeholder/pattern-placeholder.mp4` — both generated locally
  (no stock footage/images, no external dependency), committed to the repo, and
  trivial to swap: replace the file in place and rebuild.

### Setting this up on a fresh clone / new project

The page itself is a normal WordPress Page (its template assignment lives in the
database, not in version control), so after a fresh install you need to create it
once:

1. WP admin → Pages → Add New.
2. Title it "Pattern Library" (the slug becomes the URL — set it to `pattern-library`
   if it doesn't default to that).
3. In the sidebar, set **Template** to "Pattern Library".
4. Publish. Visit `/pattern-library` while logged in.

No other setup is needed — every existing and future block appears automatically once
the page exists.

### Dev-only "Components" section

`/pattern-library` also has a second section for base design-system pieces — Blade
`@props` components (`<x-input>`, `<x-alert>`, `<x-icon>`, etc.) plus non-component
style primitives (buttons, the type scale, the callout style). This section only
renders when `WP_DEBUG` is true (on by default locally via
`site/config/environments/development.php`, off everywhere else via WP core's own
fallback) — it's for development, never shown to a client regardless of login state
or environment.

**Workflow when adding a new component** (`site/web/app/themes/sage/resources/views/components/*.blade.php`):

1. Build the component as normal — it's auto-discovered by
   [`PatternLibrary::components()`](site/web/app/themes/sage/app/View/Composers/PatternLibrary.php)
   just by existing in that folder, no registration step.
2. Add an example file at `resources/views/components/examples/{component-name}.blade.php`
   containing literal sample usage, e.g. for a new `<x-card>`:

   ```blade
   {{-- resources/views/components/examples/card.blade.php --}}
   <x-card title="Example card">Example body copy.</x-card>
   ```

   Stack more than one line in the same file to show multiple variants (see
   `resources/views/components/examples/input.blade.php` for both the text and
   textarea variants of `<x-input>`).
3. That's it — visit `/pattern-library` locally and the component shows up under
   "Components (dev only)" with your example rendered.

Skip step 2 and the component still lists (so the gap stays visible rather than
silently missing), just with a "no example yet" note instead of a rendered preview.

Buttons/type-scale/callout aren't separate files, so they can't be auto-discovered
the same way — those are hand-maintained in
[`resources/views/partials/pattern-library-styles.blade.php`](site/web/app/themes/sage/resources/views/partials/pattern-library-styles.blade.php).
Only touch that file if you're introducing a genuinely new style primitive (not a
component, not a block) — rare.

# [Web Awesome](https://webawesome.com/)

`@awesome.me/webawesome` (from the Font Awesome team) is a library of framework-agnostic, MIT-licensed web components (`<wa-button>`, `<wa-card>`, `<wa-dialog>`, `<wa-input>`, etc.). This project only depends on the free npm package — no paid Pro tier dependency.

- Styles are registered globally via `import '@awesome.me/webawesome/dist/styles/webawesome.css';` in `resources/js/app.js`. Not imported in `resources/js/editor.js`, so components aren't available inside the block editor yet.
- **Components must be imported individually, per-component**, e.g. `import '@awesome.me/webawesome/dist/components/button/button.js';`. Currently imported: `button`, `card`. Add a new import line the first time a new `<wa-*>` tag is used.

### ⚠️ Don't import `@awesome.me/webawesome/dist/webawesome.js`

That entry point is a CDN-style autoloader, not a bundle — it lazy-fetches each component's JS at runtime by detecting its own `<script src="webawesome.js">` tag on the page to compute a base path. Bundled through Vite, no such script tag exists, so base-path detection silently fails and `<wa-*>` tags never register as custom elements — **no console error, no failed network request**, they just render as inert, unstyled markup (buttons that look like plain text and aren't clickable, links with `href` set but no working anchor underneath). This bit us once already: see [site/web/app/themes/sage/app/Blocks/CtaStrip.php](site/web/app/themes/sage/app/Blocks/CtaStrip.php)'s button silently failing to be clickable on the front end until the import was switched to per-component. Cherry-picking imports (Web Awesome's own npm/bundler install guidance) avoids this entirely.

Full component docs/props/slots/events: https://webawesome.com/docs/components/. To check what's actually available locally (versions can drift from the docs), list `site/web/app/themes/sage/node_modules/@awesome.me/webawesome/dist/components/`.
