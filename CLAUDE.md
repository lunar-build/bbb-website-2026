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

**ACF Composer** (`log1x/acf-composer`) provides an Artisan-like CLI for defining ACF field groups and Gutenberg blocks in PHP instead of the ACF UI. Blocks live in `app/Blocks/*.php` with a matching Blade view at `resources/views/blocks/<kebab-block-name>.blade.php` (auto-discovered, no manual registration). Standalone field groups go in `app/Fields/`; global options pages go in `app/Options/*.php` extending `Log1x\AcfComposer\Options` (see `app/Options/ThemeOptions.php`, read via `get_field('field_name', 'option')`). **For the full block-building workflow** — scaffolding, `fields()`/`with()`, ACF fields vs. `InnerBlocks`, the `<section>` root convention, `$mode`/cache gotchas, Web Awesome/Lunar UI usage, and the WP/ACF-data-shaping convention — see the `build-acf-block` skill (`.claude/skills/build-acf-block/SKILL.md`); that skill is the source of truth for block work, not this file.

**Web Awesome** (`@awesome.me/webawesome`) and **Lunar UI** (`@lunar.build/lunar-ui-components`, `node_modules/@lunar.build/lunar-ui-components`, currently `<lunar-nav>`, `<lunar-site-header>`, `<lunar-site-footer>`) are both framework-agnostic web component libraries, both registered globally in `resources/js/app.js` (not `editor.js`, so unavailable in the block editor). Full usage details, import gotchas, and the data-shaping convention for passing structured props into either are in the `build-acf-block` skill, not duplicated here.

## Notes

- `web/app/plugins/advanced-custom-fields` and WordPress core (`web/wp`) are dependency-managed via Composer (see repositories in `site/composer.json`, including a private `advanced-custom-fields-pro` VCS repo) — don't edit them directly.
- The Vite dev server binds `0.0.0.0:5173` and derives its public origin from `DDEV_PRIMARY_URL_WITHOUT_PORT`; CORS is restricted to `*.test` hosts.
