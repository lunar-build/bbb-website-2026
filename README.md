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

## Building a new block — use the `build-acf-block` skill

Repo ships a [Claude Code](https://claude.com/claude-code) skill at [.claude/skills/build-acf-block/SKILL.md](.claude/skills/build-acf-block/SKILL.md) that walks through the whole block-creation workflow so it's consistent across the team. Ask Claude Code to "create a block" (or invoke `/build-acf-block`) and it will:

1. Work out whether the block's UI needs plain Blade, a Web Awesome component (`<wa-button>`, `<wa-card>`, etc.), or a Lunar UI component (`<lunar-*>`).
2. If it needs a **new** Lunar component that doesn't exist yet, it stops and prints a ready-to-paste prompt for you to run in a separate Claude Code session inside your local [`ui-components`](https://github.com/lunar-build/ui-components) checkout — the skill never edits that repo directly. Once you've built + published the component there, bump `@lunar.build/lunar-ui-components` in this theme's `package.json`, `npm install`, and continue.
3. Scaffold the block with `wp acorn acf:block "Block Name"` (run this interactively, not piped through a script — see the skill file for why) and wire up `fields()`/`with()`/the Blade view following the existing blocks as reference.
4. Verify the block registers and renders, including checking real front-end output, not just the editor preview.

See `site/web/app/themes/sage/app/Blocks/FeatureCard.php` + `.../resources/views/blocks/feature-card.blade.php` for a worked example built via this workflow (Web Awesome `<wa-card>`/`<wa-button>`, no Lunar component needed for this one).

# [Web Awesome](https://webawesome.com/)

`@awesome.me/webawesome` (from the Font Awesome team) is a library of framework-agnostic, MIT-licensed web components (`<wa-button>`, `<wa-card>`, `<wa-dialog>`, `<wa-input>`, etc.). This project only depends on the free npm package — no paid Pro tier dependency.

- Styles are registered globally via `import '@awesome.me/webawesome/dist/styles/webawesome.css';` in `resources/js/app.js`. Not imported in `resources/js/editor.js`, so components aren't available inside the block editor yet.
- **Components must be imported individually, per-component**, e.g. `import '@awesome.me/webawesome/dist/components/button/button.js';`. Currently imported: `button`, `card`. Add a new import line the first time a new `<wa-*>` tag is used.

### ⚠️ Don't import `@awesome.me/webawesome/dist/webawesome.js`

That entry point is a CDN-style autoloader, not a bundle — it lazy-fetches each component's JS at runtime by detecting its own `<script src="webawesome.js">` tag on the page to compute a base path. Bundled through Vite, no such script tag exists, so base-path detection silently fails and `<wa-*>` tags never register as custom elements — **no console error, no failed network request**, they just render as inert, unstyled markup (buttons that look like plain text and aren't clickable, links with `href` set but no working anchor underneath). This bit us once already: see [site/web/app/themes/sage/app/Blocks/CtaStrip.php](site/web/app/themes/sage/app/Blocks/CtaStrip.php)'s button silently failing to be clickable on the front end until the import was switched to per-component. Cherry-picking imports (Web Awesome's own npm/bundler install guidance) avoids this entirely.

Full component docs/props/slots/events: https://webawesome.com/docs/components/. To check what's actually available locally (versions can drift from the docs), list `site/web/app/themes/sage/node_modules/@awesome.me/webawesome/dist/components/`.

# [Lunar UI Components](https://github.com/lunar-build/ui-components)

`@lunar.build/lunar-ui-components` is a small Lit-based web component library (`<lunar-nav>`, `<lunar-site-header>`, `<lunar-site-footer>`), registered globally via `import '@lunar.build/lunar-ui-components/main.js';` in `resources/js/app.js`, the same pattern used for Web Awesome. Component styles are bundled per-component (Lit Shadow DOM) — nothing extra to import for that.

### Shaping WP/ACF data for structured component props

These components take structured `Array`/`Object` props (e.g. `lunar-nav`'s `items`, `lunar-site-footer`'s `columns`/`legal`), and WordPress's native data (menu items, ACF fields) never matches that shape 1:1 — a small conversion step is needed every time.

**Why it works with plain HTML attributes:** Lit auto-`JSON.parse`s a matching HTML attribute for any property declared `{ type: Array }` or `{ type: Object }` with no custom `converter` — that's the default behaviour of Lit's reactive property system, not something specific to these components. So Blade never needs a JS bridge (Alpine, a data attribute + manual `JSON.parse` in JS, etc.) to get structured data into one of these components — it just writes a JSON string into the attribute and Lit does the parsing itself:

```blade
<lunar-nav items="{{ json_encode($shaped) }}">
```

**Why a conversion step is still needed:** the JSON just has to be *valid* — it also has to match the exact key names/shape the component's `static properties` declares. WordPress data almost never lines up with that out of the box. A minimal example: `<lunar-nav>`'s `items` prop expects `{ label, href, current, children }[]`, but `wp_get_nav_menu_items()` returns objects shaped like `{ title, url, menu_item_parent, ID, ... }` — different key names, and a flat list keyed by parent ID instead of a nested tree. Passing the raw WP data straight into `items="{{ json_encode($menu_items) }}"` would produce valid JSON that the component still can't read correctly, since it's looking for `label`/`href`/`children` that don't exist on it.

Convention for this and any future component library used the same way:

1. Check the component's source (its `static properties` block, for Lit components) to confirm the exact shape it expects.
2. Write one small pure function converting WP/ACF data → that shape, in [site/web/app/themes/sage/app/helpers.php](site/web/app/themes/sage/app/helpers.php) (autoloaded via `composer.json`'s `autoload.files`), named `thing_to_shape()`.
3. Reuse it across blocks rather than re-deriving the same mapping inline in Blade.
4. Keep the Blade view thin: call the helper, `json_encode()` the result into the attribute.

**Worked example — `menu_items_to_array()`:** converts a flat, parent-ID-keyed WP menu into the nested shape above:

```php
// app/helpers.php
function menu_items_to_array(string $location): array
{
    $menu_items = wp_get_nav_menu_items(get_nav_menu_locations()[$location]);

    $by_parent = [];
    foreach ($menu_items as $item) {
        $by_parent[(int) $item->menu_item_parent][] = $item;
    }

    $build = function (int $parent_id) use (&$build, $by_parent): array {
        return array_map(fn ($item) => [
            'label' => $item->title,
            'href' => $item->url,
            'current' => (bool) $item->current,
            'children' => $build((int) $item->ID), // recurse for nested submenus
        ], $by_parent[$parent_id] ?? []);
    };

    return $build(0); // start from the top-level items (parent id 0)
}
```

```blade
{{-- resources/views/sections/header.blade.php --}}
@php($items = menu_items_to_array('primary_navigation'))

<lunar-nav items="{{ json_encode($items) }}">
```

`menu_items_to_footer_columns()` builds on top of this rather than re-walking the menu: it calls `menu_items_to_array()` first, then buckets the already-shaped top-level items into `columns` (ones with children) vs `legal` links (ones without), for `<lunar-site-footer>`'s `{ columns, legal }` shape.

See [site/web/app/themes/sage/resources/views/sections/header.blade.php](site/web/app/themes/sage/resources/views/sections/header.blade.php) and [.../sections/footer.blade.php](site/web/app/themes/sage/resources/views/sections/footer.blade.php) for the full canonical usage.

### Is there one generic converter, or one per block?

**One small pure function per component prop *shape*, not one per block, and not one generic converter.** Lit's auto-JSON-parse only guarantees the *wire format* works (valid JSON in an attribute) — it says nothing about whether the *keys* inside that JSON match what the component expects. ACF/WP data almost never uses the same key names a Lunar component declares in its `static properties`, so there's no way to write a single converter that handles every case; the conversion is always "shape A → shape B," and shape B is fixed by whichever component you're feeding.

**Worked example — a block combining a repeater, a WYSIWYG field, a CTA link, and a table:** say a block needs all four field types, feeding a hypothetical `<lunar-feature-grid>` that declares:

```js
static properties = {
  items: { type: Array },   // [{ title, description, cta: { label, href } }]
  intro: { type: String },  // raw HTML string
  table: { type: Object },  // { headers: string[], rows: string[][] }
};
```

- **Repeater → `items`:** an ACF repeater returns rows like `{ card_title, card_body, card_link }` — different key names, and `card_link` is ACF's link-field shape (`{ title, url, target }`), not the component's `{ label, href }`. One helper, named after the shape it produces:

  ```php
  // app/helpers.php
  function repeater_to_feature_grid_items(array $rows): array
  {
      return array_map(fn ($row) => [
          'title' => $row['card_title'],
          'description' => $row['card_body'],
          'cta' => [
              'label' => $row['card_link']['title'] ?? '',
              'href' => $row['card_link']['url'] ?? '',
          ],
      ], $rows);
  }
  ```

- **WYSIWYG → `intro`:** often needs *no* helper at all — `get_field('intro')` already returns an HTML string, and if the component just wants a plain `String`, pass it straight through. Not every field needs a conversion function; only ones where the shape actually mismatches.

- **CTA link → a single `{ label, href }` prop:** if it's a single ACF link field feeding a single prop (not a whole array), that's usually just a 3-4 line inline transform in the block's `with()` method — it doesn't earn its own named helper unless it's reused by more than one block. Compare `CtaStrip::link()`, which needs *no* transform at all because `wa-button`'s `href`/label are passed as separate Blade attributes rather than one structured prop.

- **Table → `table`:** a third, separate helper, `table_rows_to_grid(array $rows): array`, walking ACF's table/repeater rows into `{ headers, rows }`.

```php
// app/Blocks/FeatureGrid.php
public function with(): array
{
    return [
        'items' => repeater_to_feature_grid_items($this->items()),
        'intro' => $this->intro(), // WYSIWYG, no reshaping needed
        'table' => table_rows_to_grid($this->tableRows()),
    ];
}
```

```blade
{{-- resources/views/blocks/feature-grid.blade.php --}}
<lunar-feature-grid
  items="{{ json_encode($items) }}"
  intro="{{ $intro }}"
  table="{{ json_encode($table) }}"
>
```

**Why this doesn't need one helper per block:** the helpers are named after the *shape they produce* (`repeater_to_feature_grid_items`, `table_rows_to_grid`), not after the block. If a second, unrelated block later also needs to feed a `<lunar-feature-grid>`, it reuses `repeater_to_feature_grid_items()` as-is — same pattern as `menu_items_to_footer_columns()` reusing `menu_items_to_array()` above. The source ACF field type (repeater vs. group vs. post meta) doesn't matter to the helper, only that its output matches the component's declared shape exactly.
