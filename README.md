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

### Troubleshooting: site `composer install` prompts for a GitHub token / ACF Pro fails to download

`composer.json` pulls ACF Pro from a private repo (`lunar-build/advanced-custom-fields-pro`). If `composer install` fails with a 404 downloading `wpengine/advanced-custom-fields`, or you're never prompted for a token and it just fails silently:

1. Delete `site/composer.lock`
2. Re-run `ddev exec "composer install"` — this forces Composer to re-resolve and it should prompt for a GitHub token
3. Generate a token at https://github.com/settings/tokens (needs access to the `lunar-build` org / the private repo) and paste it in when prompted

Composer caches this token in `auth.json` (gitignored) so you shouldn't be prompted again after the first successful install.

### First run / after installing WordPress

Once WP install screen has run (site loads, DB set up):

1. Grab `ACF_PRO_KEY` from 1Password and set it in `site/.env`
2. Activate ACF Pro plugin in WP admin (Plugins)
3. Confirm active theme is **Sage** (`web/app/themes/sage`) — WP admin > Appearance > Themes

# [ACF Composer](https://github.com/log1x/acf-composer)

Provides an artisan-like CLI for managing ACF fields and blocks in a WordPress project. Checkout the link above for example usage and documentation. See the below example.

## [TextHero Block](site/web/app/themes/sage/app/Blocks/TextHero.php)

Blade file: [site/web/app/themes/sage/resources/views/blocks/text-hero.blade.php](site/web/app/themes/sage/resources/views/blocks/text-hero.blade.php)

This block uses the core/heading and core/paragraph blocks as content. The block is registered in the `TextHero.php` file and the fields are defined in the `fields()` method. The block is rendered in the `with()` method, which passes the block data to the Blade template. The `template` property defines the default inner blocks for the block.

# [Lunar UI Components](https://github.com/lunar-build/ui-components)

`@lunar.build/lunar-ui-components` is a small Lit-based web component library (`<lunar-nav>`, `<lunar-site-header>`, `<lunar-site-footer>`), registered globally via `import '@lunar.build/lunar-ui-components/main.js';` in `resources/js/app.js`, the same pattern used for Web Awesome. Component styles are bundled per-component (Lit Shadow DOM) — nothing extra to import for that.

**Shaping WP/ACF data for structured component props:** these components take structured `Array`/`Object` props (e.g. `lunar-nav`'s `items`, `lunar-site-footer`'s `columns`/`legal`), and WordPress's native data (menu items, ACF fields) never matches that shape 1:1 — a small conversion step is needed every time. Handily, Lit auto-`JSON.parse`s a matching HTML attribute for `Array`/`Object`-typed props with no custom converter, so Blade can just write `items="{{ json_encode($shaped) }}"` — no JS bridge or extra dependency (e.g. Alpine) needed.

Convention for this and any future component library used the same way:

1. Check the component's source (its `static properties` block, for Lit components) to confirm the exact shape it expects.
2. Write one small pure function converting WP/ACF data → that shape, in [site/web/app/themes/sage/app/helpers.php](site/web/app/themes/sage/app/helpers.php) (autoloaded via `composer.json`'s `autoload.files`), named `thing_to_shape()`.
3. Reuse it across blocks rather than re-deriving the same mapping inline in Blade. See `menu_items_to_array()` and `menu_items_to_footer_columns()` (the latter reuses the former) for the pattern.
4. Keep the Blade view thin: call the helper, `json_encode()` the result into the attribute.

See [site/web/app/themes/sage/resources/views/sections/header.blade.php](site/web/app/themes/sage/resources/views/sections/header.blade.php) and [.../sections/footer.blade.php](site/web/app/themes/sage/resources/views/sections/footer.blade.php) for the canonical usage.
