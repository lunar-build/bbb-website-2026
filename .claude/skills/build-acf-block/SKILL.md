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

## 1. Decide what UI each part of the block needs

For every piece of UI in the new block, pick one:

- **(a) Plain HTML/Blade** — no component library needed.
- **(b) Web Awesome directly** — `<wa-button>`, `<wa-card>`, `<wa-dialog>`, etc. Already
  used this way in `resources/views/blocks/cta-strip.blade.php` and `video-hero.blade.php`.
  Check what's available locally: `node_modules/@awesome.me/webawesome/dist/components/`.
  Docs: https://webawesome.com/docs/components/
- **(c) An existing Lunar component** — `<lunar-nav>`, `<lunar-site-header>`,
  `<lunar-site-footer>` today. Check `node_modules/@lunar.build/lunar-ui-components/components/`
  for the current list, and that component's `index.js` `static properties` block for its
  exact prop shape before wiring data into it.
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

If a field's WP/ACF shape doesn't already match what a component expects (e.g. a
repeater feeding a Lunar component's array-typed `items` prop), don't re-derive the
mapping inline in Blade — follow the `thing_to_shape()` convention already documented
in the root `CLAUDE.md` ("Shaping WP/ACF data for structured component props") and put
the pure conversion function in `app/helpers.php`, reusing `menu_items_to_array()` /
`menu_items_to_footer_columns()` as examples.

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
