---
name: build-acf-block
description: >
  Step-by-step workflow for creating a new ACF Composer block in the Sage theme
  (site/web/app/themes/sage), including a decision tree for when to use Web Awesome
  (<wa-*>) directly, an existing Lunar UI (<lunar-*>) component, or when a new Lunar
  component needs to be built in the sibling ui-components repo first. Use when the
  user says "create a block", "new block", "add a block", "build a block", or invokes
  /build-acf-block.
---

# Building an ACF Composer block

Scope: this skill only ever reads/writes inside `bbb-website-2026`. It never edits,
builds, or publishes anything in the sibling `ui-components` repo (source of
`@lunar.build/lunar-ui-components`). Where a new Lunar component is needed, this
skill's job stops at printing a spec/prompt (see step 1d and "Requesting a new Lunar
component" below) for the developer to take to that repo manually.

## 0. Ask for a Figma reference (optional)

Before starting, ask the developer: *"Do you have a Figma link for this block (a link
to a specific layer/frame/group, copied via Figma's 'Copy link to selection')?"*

If yes, and the Figma MCP server is connected in this Claude Code session, use it to
pull the layer's structure/screenshot into context before scaffolding — this gives
real spacing, copy, and component boundaries to work from instead of guessing. If the
Figma MCP server isn't connected, tell the developer how to add it
(`claude mcp add --transport http figma-dev-mode-mcp-server http://127.0.0.1:3845/mcp`,
with Figma desktop's Dev Mode MCP server enabled) and continue without it if they'd
rather not set it up now — this step is optional, never blocking.

If no link is offered, skip straight to step 1 and work from the developer's
description.

## 1. Decide what UI each part of the block needs

For every piece of UI in the new block, pick one:

- **(a) Plain HTML/Blade** — no component library needed.
- **(b) Web Awesome directly** — `<wa-button>`, `<wa-card>`, `<wa-dialog>`, etc. Already
  used this way in `resources/views/blocks/cta-strip.blade.php` and `video-hero.blade.php`.
  Check what's available locally: `node_modules/@awesome.me/webawesome/dist/components/`.
  Docs: https://webawesome.com/docs/components/

  Styles are already registered globally (`resources/js/app.js` imports
  `@awesome.me/webawesome/dist/styles/webawesome.css`). **But each component's JS must be
  imported individually** — add `import '@awesome.me/webawesome/dist/components/<name>/<name>.js';`
  to `resources/js/app.js` the first time a new `<wa-*>` tag is used (currently imported:
  `button`, `card`, `accordion`, `accordion-item`). Do **not** import `@awesome.me/webawesome/dist/webawesome.js` instead
  — that's a CDN-style autoloader that lazy-fetches components by detecting its own
  `<script src="webawesome.js">` tag to compute a base path; bundled through Vite there's
  no such tag, so it silently fails to find any component and every `<wa-*>` tag stays an
  inert, undefined custom element — no console error, no network 404, just dead markup
  (this bit `CtaStrip`'s button once already). Not imported in `resources/js/editor.js`
  either, so `<wa-*>` components aren't available inside the block editor yet.

- **(c) An existing Lunar component** — `<lunar-nav>`, `<lunar-site-header>`,
  `<lunar-site-footer>` today. Check `node_modules/@lunar.build/lunar-ui-components/components/`
  for the current list, and that component's `index.js` `static properties` block for its
  exact prop shape before wiring data into it. Registered globally the same way as Web
  Awesome, via `import '@lunar.build/lunar-ui-components/main.js';` in `resources/js/app.js`
  (styles are bundled per-component via Lit shadow DOM, nothing extra to import).

  Passing structured data in: Lit auto-`JSON.parse`s a matching HTML attribute for any
  property declared `{ type: Array }` or `{ type: Object }` with no custom `converter` —
  so Blade can just write `items="{{ json_encode($shaped) }}"` and the component parses
  it itself, no JS bridge (Alpine etc.) needed. Confirm the property's declared as
  `Array`/`Object` with no custom `converter` in the component's `index.js` before relying
  on this. `app/helpers.php` already has `menu_items_to_array()` (WP menu → `<lunar-nav
  items="...">`'s nested shape) and `menu_items_to_footer_columns()` (reuses it) as
  reference conversions — see "Shaping WP/ACF data" below for the general pattern.
- **(d) A new Lunar component is needed** — stop here. Don't scaffold the block yet.
  Jump to "Requesting a new Lunar component" below, produce the prompt, and wait for the
  developer to build + publish it in `ui-components` and bump the dependency in this
  theme's `package.json` before continuing.

## 2. Scaffold the block

From `/site`, inside DDEV:

```bash
ddev exec "wp acorn acf:block 'Block Name'"
```

This prompts interactively for:
- **Description** (free text, default `"A beautiful {Name} block."`)
- **Category** (from WP's default block categories, e.g. `text`, `media`, `design`)
- **Supported post types** (multiselect, empty = all)
- **Supported block features** (multiselect — align, mode, multiple, jsx, color.*,
  spacing.* — preselected from `config/acf.php`'s `generators.supports`, currently
  `['align', 'mode', 'multiple', 'jsx']`)

It creates exactly two files:
- `app/Blocks/{BlockName}.php`
- `resources/views/blocks/{block-name}.blade.php` (kebab-case of the class name)

**No manual registration step.** `Log1x\AcfComposer\AcfComposer` auto-discovers any
class under `app/Blocks/*.php` extending `Block` — don't add anything to
`ThemeServiceProvider` or `setup.php`.

**`$mode` is always `'preview'`, with `supports.mode` (and `supports.jsx`) both `true`.**
Every block in this theme uses this combination so editors get a rendered preview by
default and only see the ACF fields (in the block's Inspector sidebar, not an inline
edit form) once they click into the block — not the reverse. Don't leave `$mode` unset
or set it to `'edit'`/`'auto'`.

```php
/**
 * The default block mode.
 *
 * @var string
 */
public $mode = 'preview';
```

**If a block appears permanently stuck in edit mode** despite this being set correctly,
it's very likely a stale ACF Composer cache serving an old field-group/mode snapshot
from before the block's fields last changed (`config/acf.php`'s `generators.manifest`
caches compiled block/field-group definitions to `storage/framework/cache`). Clear it:
```bash
ddev exec "wp acorn acf:clear"
```
Run this any time a block's `fields()`/`$mode`/`$supports` changes and the editor
doesn't seem to reflect it — this is a real, recurring gotcha, not a one-off.

**Non-interactive pitfall:** `wp acorn acf:block` uses `laravel/prompts`, which needs a
real TTY. Run it through a normal `ddev ssh` / interactive terminal and answer the
prompts yourself. If it's invoked non-interactively (e.g. piping input through
`ddev exec` from an agent session), the prompts silently fall through to garbage
defaults — observed failure mode: the class gets generated as `class Feature Card
extends Block` (block name used verbatim, space and all, as the PHP class name) and the
file is written as `app/Blocks/Feature Card.php` (also with a literal space) — invalid
PHP, won't autoload. **After running the command, always open the generated
`app/Blocks/*.php` file and confirm**: the filename has no spaces and matches
`{BlockName}.php` in PascalCase, and the `class` declaration uses that same PascalCase
name (`class BlockName extends Block`). If either is wrong, delete both generated files
and hand-write them following the structure of an existing block (`CtaStrip.php` /
`cta-strip.blade.php` is the cleanest reference) instead of trying to patch the broken
output in place.

## 3. Wire fields

In the generated class:

- `fields()` builds the ACF field group: `Builder::make('snake_case_name')->addText(...)->build()`.
  The string passed to `make()` becomes the field group key.
- `with()` returns the array exposed to the Blade view as variables (no `$block->` prefix
  needed for these).
- Add one accessor method per field, following the existing pattern (see
  `app/Blocks/CtaStrip.php`, `app/Blocks/VideoHero.php`):
  - Text/textarea fields: `return get_field('x') ?: $this->example['x'];`
  - Link fields: fallback is a hand-built array matching ACF's link shape —
    `['title' => ..., 'url' => ..., 'target' => '']`.
  - Image/file/relationship fields: no fallback, just `return get_field('x');`.
- `$example` on the class supplies the block-editor preview/empty-state data used by
  every accessor's fallback.

**ACF fields vs. `InnerBlocks` for freeform copy:** a block's editable content can come
from either ACF fields (`addText`/`addTextarea`, `get_field()`-backed, as above) or
native `InnerBlocks` (empty `fields()`, a `$template` property, `<InnerBlocks
template="{{ $block->template }}" />` in Blade — see `app/Blocks/TextHero.php`). Prefer
`InnerBlocks` for a block's heading/body copy: it gives real, document-outline-correct
HTML elements (`core/heading` with a CMS-editable H1–H6 level, `core/paragraph`) for
free, whereas ACF text/textarea fields require you to hand-build that semantics
yourself (see `app/Blocks/FeatureCard.php`/`CtaStrip.php`, which use `InnerBlocks` for
this reason). A block can mix both: keep an ACF field for anything `InnerBlocks` has no
native equivalent for (e.g. a CTA button's `link` field, which stays ACF in both of
those blocks since there's no core block for a styled `wa-button`). Known trade-off:
`InnerBlocks` content isn't covered by `$example`/get_field() fallbacks, so a block's
editor "preview" thumbnail shows the raw `$template` placeholders rather than curated
example copy — accepted as-is for now.

## 4. Build the Blade view

Standard wrapper pattern (see `text-hero.blade.php`, `cta-strip.blade.php`):

```blade
@unless ($block->preview)
  <section {{ $attributes->class(['c-block-name']) }}>
@endunless

  {{-- block content --}}

@unless ($block->preview)
  </section>
@endunless
```

**Root element is always `<section>`, never `<div>`.** Every block is a distinct
region of page content, so `<section>` is the correct semantic wrapper — this is a
fixed convention, not a per-block choice. If a block's content already has its own
inner `<section>` (e.g. an "inner" wrapper), rename that inner one to `<div>` rather
than nesting two `<section>`s (see `video-hero.blade.php`'s `.c-video-hero__inner`).

`$attributes->class([...])` merges Gutenberg's wrapper classes with your own BEM root
class. Use `$attributes` bare (no `->class()`) if you don't need an extra class.

### Shaping WP/ACF data for structured component props

Whenever a block's Blade view hands data to a component expecting a structured
`Array`/`Object` prop (Lunar's `items`/`columns`/`legal`, or similar on any future
component library), WordPress's native data shape (menu items, ACF repeater rows,
`WP_Query` results) won't match the component's expected shape 1:1 — a small conversion
step is needed every time. One small pure function per **shape**, not per block or one
generic converter (ACF/WP key names almost never match a component's declared prop
names, so there's no way to write a single converter that handles every case):

1. Check the component's source (its `static properties` block, for Lit components) to
   confirm the exact keys/shape it expects.
2. Write one small pure function converting WP/ACF data → that shape, named after the
   *shape it produces* (e.g. `menu_items_to_array`, `repeater_to_feature_grid_items`),
   not after the block that happens to use it first — so a later, unrelated block
   needing the same shape reuses it as-is instead of duplicating it.
3. Put it in `app/helpers.php` (autoloaded via `composer.json`'s `autoload.files`).
   See `menu_items_to_array()` and `menu_items_to_footer_columns()` (the latter reuses
   the former rather than re-walking the menu) for the pattern.
4. Keep the Blade view thin: call the helper, `json_encode()` the result into the
   attribute, done. Not every field needs a helper — if a field's raw shape already
   matches the component's prop (or the component just wants a plain string), pass it
   straight through.

## 5. Verify

- Confirm `npm run dev` (HMR) is running in `web/app/themes/sage`, or run `npm run build`.
- Insert the block in the WP block editor and confirm the preview renders using the
  `$example` fallback data.
- Check the front end via the DDEV site URL and confirm real field data renders.

---

## Requesting a new Lunar component

When step 1d applies, print a prompt like the one below (filled in for the actual block)
instead of writing any code in `ui-components`. The developer copies it into a separate
Claude Code session opened in their local `ui-components` clone.

The prompt must cover:
- **Component name**: `lunar-<name>`, kebab-case, plus a one-line purpose.
- **Props/attributes**, with types matching what the Blade view will pass in (e.g.
  `heading: String`, `link: Object` for a `{title, url, target}` shape) — Lit auto-parses
  JSON attributes for `Array`/`Object`-typed properties with no custom converter, so
  props can stay simple.
- **Composition**: which Web Awesome elements (`<wa-card>`, `<wa-button>`, etc.) it
  should wrap internally, inside the Lit component's `render()`.
- **Structural convention to follow**: one folder per component under
  `components/<name>/`, containing `index.js` + `styles.css`; scoped theming via
  `--lunar-<component>-<variant>-<property>` CSS custom properties; register in
  `components/base.css`.
- **Publishing reminder**: pushing to `main` on `ui-components` auto-bumps the npm
  patch version and publishes via CI — that's a deliberate, separate step the developer
  triggers themselves once the component is reviewed, not something to do automatically.
- **Return step**: once published, bump `@lunar.build/lunar-ui-components` in this
  theme's `package.json`, run `npm install`, then resume the block build from step 2.

### Example generated prompt

```
Build a new Lunar UI component: <lunar-feature-card>

Purpose: a bordered card with a heading, body text, and a single CTA button —
used inside a Sage theme ACF block.

Props:
  heading: String
  body: String
  link: Object   // { title: String, url: String, target: String }

Composition: wrap <wa-card appearance="outlined" with-footer> for the body and
<wa-button slot="footer" variant="brand"> for the CTA, per this repo's convention
of composing Web Awesome elements inside a Lit render().

Follow the existing structural pattern: components/lunar-feature-card/{index.js,styles.css},
scoped theming vars like --lunar-feature-card-bg, register in components/base.css.

Once built and reviewed, push to main to publish (auto-bumps npm patch version) —
that's your call, not something to do as part of scaffolding.
```
