# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Repository layout

This repo has two levels:

- `/` (repo root) — just this file, `.vscode/tasks.json`, and a top-level `README.md`.
- `/site` — the actual Bedrock/WordPress project root. Almost all work happens here.
  - `/site/web/app/themes/sage` — the Sage 10 theme, where nearly all custom PHP/JS/CSS development happens.

The project runs inside DDEV (Docker). The DDEV project name is `bbb-rebuild` (PHP 8.4, nginx-fpm, MariaDB 11.8), site URL `https://bbb-rebuild.test`.

When VS Code opens the workspace, tasks auto-start Docker + `ddev start` and open two SSH terminals: one in `/site` and one in `/site/web/app/themes/sage` (see `.vscode/tasks.json`). Assume DDEV is already running unless a command fails because it isn't.

## Common commands

All PHP/composer/wp-cli commands must run inside the DDEV container. From `/site`:

```bash
ddev start                                   # start the environment
ddev composer install                        # install Bedrock-level PHP deps
ddev exec "npm --prefix web/app/themes/sage install"
ddev exec "composer --working-dir web/app/themes/sage install"
```

Theme front-end build (run from `/site/web/app/themes/sage`, typically via the `ssh:sage` DDEV shell):

```bash
npm run dev      # Vite dev server with HMR
npm run build    # production build (writes to public/build)
```

Testing and linting (Pest + Pint, run from `/site`, e.g. via `ddev composer <script>` or inside `ddev ssh`):

```bash
composer test         # runs `pest`
composer lint         # `pint --test` (check only)
composer lint:fix      # `pint` (auto-fix)
```

To run a single Pest test, use the normal Pest CLI inside the container, e.g. `vendor/bin/pest tests/Feature/ExampleTest.php`.

Pint config (`/site/pint.json`) uses the `per` preset and excludes `web/wp`, `web/app/mu-plugins/bedrock-disallow-indexing`, `web/app/plugins`, and `web/app/themes/twentytwentyfive` — never lint/format vendored WordPress core, plugin, or default-theme code.

Theme translations (from `/site/web/app/themes/sage`): `npm run translate` (pot + po update), `npm run translate:compile` (mo + js).

## Architecture

**Bedrock** (`/site`) is the WordPress boilerplate layer: env-based config in `config/application.php` and `config/environments/`, secrets/DB config via `.env` (see `.env.example` for required keys), WordPress core installed to `web/wp` (untouched, not for editing), and `web/app` as the WordPress content directory (mu-plugins, plugins, themes, uploads).

**Sage 10** (`web/app/themes/sage`) is the active theme, built on Laravel's Acorn (`roots/acorn`) for Laravel-style structure inside WordPress:

- `app/setup.php` — theme bootstrap: enqueues editor assets via `Vite::asset`/`Vite::withEntryPoints`, wires the generated `theme.json` (built to `public/build/assets/theme.json` by the Vite `wordpressThemeJson` plugin — don't hand-edit the built one), registers nav menus/sidebars, and sets standard `add_theme_support` flags.
- `app/filters.php` — WordPress filter hooks.
- `app/Providers/ThemeServiceProvider.php` — extends Acorn's `SageServiceProvider`; register additional container bindings/services here.
- `app/View/Composers/*` — Blade view composers (data shared with specific views), auto-discovered by Acorn.
- `resources/views/*.blade.php` — Blade templates mapped from standard WP template hierarchy (`index`, `page`, `single`, `search`, `404`, plus `layouts/`, `partials/`, `sections/`, `components/`, `forms/`, `blocks/`).
- `resources/{css,js}/app.*` and `editor.*` — separate front-end and block-editor entry points, built by Vite (`vite.config.js`). Path aliases: `@scripts`, `@styles`, `@fonts`, `@images` map to `resources/js`, `resources/css`, `resources/fonts`, `resources/images`.
- `config/acf.php` — ACF configuration.

**ACF Composer** (`log1x/acf-composer`) provides an Artisan-like CLI for defining ACF field groups and Gutenberg blocks in PHP instead of the ACF UI:

- Blocks live in `app/Blocks/*.php` (e.g. `app/Blocks/TextHero.php`), each extending `Log1x\AcfComposer\Block`. Public properties configure block metadata (name, category, icon, supports, alignment, styles, `example` preview data, `template` for default inner blocks). `fields()` builds the ACF field group via `Builder::make(...)`. `with()` returns data passed into the block's Blade view. Helper methods (e.g. `heading()`, `tagline()`) wrap `get_field()` with fallbacks to `$this->example`.
- Each block's Blade view lives at `resources/views/blocks/<kebab-block-name>.blade.php`.
- Standalone field groups (not tied to a block) go in `app/Fields/`.

When adding a new block: create the `App\Blocks\*` class, define fields with `Builder`, add the matching Blade view under `resources/views/blocks/`, and reference `app/Blocks/TextHero.php` / `resources/views/blocks/text-hero.blade.php` as the canonical example.

**Shaping WP/ACF data for structured component props:** whenever a block's Blade view hands data to a component that expects a structured `Array`/`Object` prop (Lunar's `items`/`columns`/`legal`, or similar on any future component library), WordPress's native data shape (menu items, ACF repeater rows, `WP_Query` results) will not match the component's expected shape 1:1 — a small conversion step is required every time, not just for Lunar. Convention:

1. Check the component's source (e.g. its `static properties` block, for Lit components) to confirm the exact keys/shape it expects.
2. Write one small pure function converting WP/ACF data → that shape, named `thing_to_shape()`.
3. Put it in `app/helpers.php` (autoloaded via `composer.json`'s `autoload.files`) and reuse it across blocks — don't re-derive the same walker/mapping inline in Blade each time. See `menu_items_to_array()` and `menu_items_to_footer_columns()` (the latter reuses the former rather than re-walking the menu) for the pattern.
4. Keep the Blade view thin: call the helper, `json_encode()` the result into the attribute, done.

## Web Awesome

[Web Awesome](https://webawesome.com/) (`@awesome.me/webawesome`, from the Font Awesome team) is a library of framework-agnostic, MIT-licensed web components (`<wa-button>`, `<wa-card>`, `<wa-dialog>`, `<wa-input>`, etc.). This project only depends on the free npm package, which ships the full **core** component set — there is no paid Pro tier dependency wired in.

- The whole library is registered globally by importing its bundled CSS and JS in the front-end entry point, `resources/js/app.js`:
  ```js
  import '@awesome.me/webawesome/dist/styles/webawesome.css';
  import '@awesome.me/webawesome/dist/webawesome.js';
  ```
  This self-registers every `<wa-*>` custom element and its styles for the public-facing site. It is **not** imported in `resources/js/editor.js`, so components aren't currently available inside the block editor.
- Because components are just custom elements, use them directly in Blade views (e.g. `<wa-button variant="brand">Submit</wa-button>`) with no additional PHP/JS wiring needed — no components are in use in `resources/views/` yet, so there's no existing in-repo usage pattern to follow.
- Full component docs/props/slots/events: https://webawesome.com/docs/components/. To check what's available locally (versions can drift from the docs), list `node_modules/@awesome.me/webawesome/dist/components/`.

## Lunar UI Components

`@lunar.build/lunar-ui-components` (`node_modules/@lunar.build/lunar-ui-components`) is a small internal Lit-based web component library — currently `<lunar-nav>`, `<lunar-site-header>`, `<lunar-site-footer>`. Registered globally the same way as Web Awesome, via `import '@lunar.build/lunar-ui-components/main.js';` in `resources/js/app.js`. Component styles are already bundled per-component (Lit shadow DOM).

**Passing PHP/Blade data into a component's `Array`/`Object`-typed properties:** Lit's default property converter auto-`JSON.parse`s a matching HTML attribute for any property declared `{ type: Array }` or `{ type: Object }` with no custom `converter` (see `node_modules/@lit/reactive-element/development/reactive-element.js`). So Blade can write a plain JSON string attribute — e.g. `items="{{ json_encode($items) }}"` for `<lunar-nav items="...">` — and the component parses it itself; no JS bridge or extra dependency (Alpine, etc.) is needed. Before relying on this for a new component/prop, check the component's `index.js` `static properties` block confirms the type and that no custom `converter` is set. Only reach for something like Alpine if a prop needs true property assignment (non-JSON-safe values, functions, DOM refs) or the block needs standalone client interactivity.

`app/helpers.php` (autoloaded via `composer.json`'s `autoload.files`) holds `menu_items_to_array(string $location): array`, which converts a WP nav menu theme location into the nested `{ label, href, current, children }` shape `<lunar-nav items="...">` expects — reuse it for any other block/section that needs menu data in this shape rather than re-deriving the walker. See `resources/views/sections/header.blade.php` for the canonical usage with `<lunar-site-header>` + `<lunar-nav>`.

## Notes

- `web/app/plugins/advanced-custom-fields` and WordPress core (`web/wp`) are dependency-managed via Composer (see repositories in `site/composer.json`, including a private `advanced-custom-fields-pro` VCS repo) — don't edit them directly.
- The Vite dev server binds `0.0.0.0:5173` and derives its public origin from `DDEV_PRIMARY_URL_WITHOUT_PORT`; CORS is restricted to `*.test` hosts.
