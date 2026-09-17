---
name: comment-restriction
description: Clean up code comments in WordPress Sage theme — remove AI-generated over-commenting, narration, and noise while preserving comments that carry real context. Use whenever the user asks to clean, tidy, prune, fix, audit, or review comments in Sage theme files (PHP, Blade templates, config), mentions "too many comments", "verbose comments", "comment pass", "de-AI the comments", or asks to apply comment guidelines to a Sage file, controller, template, or PR. Also use after an implementation when the user asks to tidy up before committing.
---

# Comment Cleanup for WordPress Sage Theme

A comment-only editing pass. Walk every comment in the target files, keep the few
that carry context the code cannot, delete or rewrite the rest.

## Scope — read this first

**Edit comments only. Never change code in this pass.** No renames, no
extractions, no logic changes, no "improvements" — even when a comment exists
only because the code is unclear. In that case the comment may survive the pass
(it is doing real work, badly) and the situation goes in the summary as a flag
for the user to act on. The user may separately ask for refactors; that is a
different task.

Whitespace left behind by deleted comments should be tidied (no orphaned blank
lines), but the code itself must be byte-identical.

Applies to:

- **PHP files** — controllers, service providers, utility functions, ACF setup
- **Blade templates** — `.blade.php` files in `resources/views`
- **Theme config** — `theme.json`, `config/theme.php`, setup/registration arrays
- **Filter/hook registrations** — actions and filters (often heavily over-commented)

## The core principle

Code already shows **how**. A comment earns its place only by carrying **why**
— context the code cannot: a non-obvious constraint, a deliberate deviation, a
gotcha, a workaround, the reason a tempting simpler version is wrong. When in
doubt about a _why_ comment, keep it; when in doubt about a _how_ comment,
delete it. Wrongly deleting a warning that does real work costs far more than leaving
one mediocre comment behind.

## The pass — apply to every comment, in order

**1. Is it narration?** Restates the code, describes the steps, restates names/types,
or marks block ends. Common Sage-specific narration to delete:

- `// Get the post ID`, `// Get post metadata`, `// Query posts`
- `// Add the action`, `// Register the filter`, `// Set up the hook`
- `// Loop through the array`, `// Return the result`
- Blade template comments like `<!-- Output the title -->`, `<!-- Check if post exists -->`
- `} // end if`, `} // end foreach`
- Over-explaining Blade syntax: `<!-- Use @foreach to loop -->`, `<!-- The $title variable -->`

→ **Delete.** This is the bulk of agent-generated comment noise. The code below it was fine all along; do not touch it.

**2. Is it change narration?** Addressed to the reviewer of a diff rather than
a reader of the file: "updated to use Sage helpers", "added ACF field", "removed deprecated hook",
"per client feedback", "fixed the query bug". → **Delete.** Test: would the sentence make sense
to someone reading the file fresh, a year from now, who never saw any PR? If not, it belongs in
a commit message.

**3. Does it point at a moving target?** "See the client brief", "per requirements doc",
"check the design file", "see the Figma mockup". → **Delete** if the annotated point didn't
need a comment at all, or **rewrite** to encode the substance directly. What counts as durable:

- **Durable (allowed, as breadcrumbs):** issue/ticket IDs (`SITE-1234`), Sage docs,
  WordPress.org codex links, permalinked design specs, ACF field group IDs in code,
  stable paths within the theme (`resources/COMPONENT_GUIDE.md`).
- **Ephemeral (rewrite or delete):** "the design", "the brief", "the Figma", section numbers
  (`design §7`), link-only comments that don't explain the _why_.

Durable references are _breadcrumbs_, never the explanation itself. The comment must stand
on its own:

```php
// Bad:  per Figma spec
// Bad:  see the brief section 3
// Good: This sidebar should not appear on mobile below 768px breakpoint — design shows
//       it collapses to accordion on tablets (SITE-489).
```

**4. Is it a _why_ that's bloated or misplaced?** For comments that do carry
real rationale, in sequence:

- _Does the rationale belong in a doc, not here?_ System-level "why it's built this way"
  — an architecture choice, hook strategy, ACF setup pattern — reads better in a maintained
  README or `ARCHITECTURE.md` than inline. If a durable doc **already** carries it → delete
  the inline copy; do **not** leave a signpost. If no such doc exists, keep the comment but
  **flag it** as extraction-worthy. Extraction is _pure_: once the narrative lives in the doc,
  what survives inline is only the local invariant a reader needs _at that line_.

- _Is all of it necessary?_ Trim throat-clearing, restated context, and anything the code shows.
  Keep every part that prevents a future maintainer from breaking or "simplifying" the code.

- _Does it belong in one block?_ A long header block often serves better broken up, with
  each short comment placed adjacent to the line it governs. Proximity keeps comments true.

- _Is the remaining length earned?_ A workaround plus the bug plus the deprecation plan may
  need three sentences. Length alone is not a defect — unearned length is.

```php
// Before (5 lines):
// This hook is called late in wp_footer to avoid conflicts with jQuery from
// WooCommerce, which loads in the default priority. Moving this to priority 100
// ensures our script loads after theirs without dependency issues.

// After (1 line):
// Late priority (100) — loads after WooCommerce jQuery to avoid conflicts
add_action('wp_footer', [$this, 'enqueueCustomScript'], 100);
```

**5. Is it stale?** Describes code that no longer exists or behavior that has changed.
Examples: commented-out code blocks, "this was needed for PHP 7.2 compatibility" (you're on 8.1+),
references to removed plugin compatibility. → **Delete** (or correct it, if the underlying point still holds).
A wrong comment is worse than no comment.

**6. None of the above?** It explains a non-obvious constraint, a surprising but essential
line, a contract the signature can't show, an edge case, the source of a copied algorithm,
or WordPress-specific gotchas. → **Keep.** Do not reword comments that are already fine.

Six keep categories worth flagging as non-deletable:

- **Hook/filter contracts** — what a filter actually expects or modifies, especially when the
  context is not obvious from the hook name. `apply_filters('sage/homepage_args', $args)` alone
  doesn't say "this is where custom post types get added" — a brief comment at the hook call
  does that work.
- **WordPress/Sage peculiarities** — "The_loop must start before this point for get_the_ID()
  to work", "Controllers run in wp_loaded, not init", "ACF blocks auto-register with blockTypes".
  These prevent subtle bugs.
- **Performance/security rationale** — "Cached to avoid N+1 queries", "Sanitized for KSES
  before output", "Nonce checked before action runs". These aren't obvious from the code alone
  and matter for security reviews.
- **Cross-file consistency pointers** — "mirrors the sidebar partial", "sync with theme.json
  color palette", "keep in sync with X partial". These prevent drift.
- **Data format contracts** — comments on return types or array structures, especially in
  Blade context: `<!-- $items: array of {id, label, active} -->`, `// Returns array of WP_Post objects`.
- **ACF/theme.json quirks** — "ACF field key is site-specific, remap on migration", "This
  matches theme.json spacing scale", "Custom post type archive must use single-archive.blade.php".

**Structural section banners** (`// --- Hook Setup ---`) are navigation, not narration — keep
them unless the user asks otherwise.

**ACF Composer `Block` property docblocks are exempt from rule 1.** `app/Blocks/*.php` classes
(`extends Log1x\AcfComposer\Block`) carry a standard scaffolded docblock on every public property
— `$name`, `$slug`, `$description`, `$category`, `$icon`, `$keywords`, `$post_types`, `$parent`,
`$ancestor`, `$mode`, `$align`, `$align_text`, `$align_content`, `$spacing`, `$supports`,
`$styles`, `$example`, `$examples` — in the form:

```php
/**
 * The block name.
 *
 * @var string
 */
public $name = 'Quote';
```

These restate the property name/type and would normally be narration → delete, but they are this
codebase's conventional block-scaffold shape (every `Block` subclass has them) and should be left
in place even though they add no information beyond the property name. This exemption covers only
these property-level docblocks — method docblocks in the same file (`with()`, `fields()`, getters,
`assets()`) still get the full six-rule pass as normal.

## TODOs and FIXMEs

Keep TODO/FIXME comments. Two exceptions:

- A TODO describing work that has visibly been done → stale, delete.
- A TODO that is pure deferral disguised as a marker — e.g. `// TODO: handle errors` on a path
  that should already handle them → keep it, but flag it; deleting it hides real incompleteness.

Sage-specific TODOs often signal future work: "TODO: migrate from ACF to native Gutenberg",
"TODO: replace with theme.json when WordPress min version is 6.0". Keep these.

## Blade Template Comments

All six rules apply inside Blade files. Additionally:

- Blade directives are often over-commented: `<!-- Start a loop with @foreach -->` → delete.
- Comments explaining Blade syntax itself (`<!-- $title is a variable -->`) → delete unless the
  value comes from an unexpected source (non-obvious scope, modifier output, computed value).
- Comments on partials should explain _why_ they're included, not _that_ they're included:

```blade
<!-- Bad -->
<!-- Include the sidebar partial -->
@include('partials.sidebar')

<!-- Good -->
<!-- Sidebar only appears on desktop — mobile collapses to bottom section -->
@if (wp_is_mobile() === false)
  @include('partials.sidebar')
@endif
```

- HTML structure comments (`<!-- Header -->`', `<!-- Main Content -->`) are navigation.
  Keep them if they mark large sections (helps readability) or if nesting is deep; delete them
  if they state the obvious next to a `<div>`.

## PHP Controllers and Service Providers

- Over-commented type hints and return statements → delete.
- Comments on public methods in controllers should explain the route behavior or filtering
  logic, not that the method exists.
- Setup methods (e.g., in service providers) often carry high-level "why we register this"
  comments — keep the substance, trim the padding.

```php
// Bad: Multiple lines just restating what the code does
public function register()
{
  // We are creating a new binding in the service container
  // This allows the dependency injection system to resolve our class
  // The key is 'logger' and the value is an instance of Logger
  $this->app->singleton('logger', new Logger());
}

// Good: Only the non-obvious constraint
public function register()
{
  // Singleton — ensure the same logger instance is used across requests
  $this->app->singleton('logger', new Logger());
}
```

## Config Arrays and Theme Registration

Comments in `config/theme.php`, `theme.json`, or filter registration arrays are often
noise. Delete comments that only list what the keys mean (the keys are self-documenting).
Keep comments that explain why a value is set to something non-obvious:

```php
// Bad
'colors' => [
  // The primary brand color
  'primary' => '#0066cc',
  // The secondary brand color
  'secondary' => '#ffaa00',
],

// Good
'colors' => [
  // Brand guidance sets primary to #0066cc for WCAG AAA contrast on white
  'primary' => '#0066cc',
  'secondary' => '#ffaa00', // Client specified in Figma v2
],
```

## Docblock Comments

All six rules apply inside docblocks. Additionally:

- A docblock that only re-emits the method name and parameter types is narration → delete or reduce.
- One that documents the contract the signature can't show (units, valid ranges, side effects,
  what happens on failure, invariants) → keep.

```php
// Bad: Only restates the signature
/**
 * Get the post.
 *
 * @param int $id The post ID
 * @return WP_Post The post object
 */

// Good: Explains non-obvious behavior
/**
 * Get the post, using cache if available.
 *
 * @param int $id The post ID
 * @param int $cache_hours Cache TTL in hours. Default 24. Set to 0 to bypass cache.
 * @return WP_Post|false The post object, or false if post doesn't exist or is not published.
 */
```

## Output

After editing, report:

1. **Counts** — comments deleted / rewritten / kept, per file.
2. **Flags** — anything the pass could not fix within scope:
   - comments compensating for genuinely unclear code (candidate refactors)
   - TODO/FIXME markers that may signal incomplete work
   - comments whose correctness couldn't be verified (kept conservatively)
   - stale or suspicious comments (wrong Sage version, old plugin compatibility)
3. **Judgment calls** — any borderline keep/delete decisions, one line each,
   so the user can overrule.

Keep the report brief. Do not list every deleted narration comment — they are
the point of the pass, not news.

## Worked Examples

**Delete — narration on clean Blade. Remove every comment:**

```blade
<!-- Bad: Over-commenting obvious template logic -->
<!-- Check if we have posts -->
@if ($posts->count() > 0)
  <!-- Loop through each post -->
  @foreach ($posts as $post)
    <!-- Display the post title -->
    <h2>{{ $post->post_title }}</h2>
    <!-- Show the post excerpt -->
    <p>{{ $post->post_excerpt }}</p>
  @endforeach
@else
  <!-- No posts found message -->
  <p>No posts found.</p>
@endif

<!-- Good: No comments — the code is self-explanatory -->
@if ($posts->count() > 0)
  @foreach ($posts as $post)
    <h2>{{ $post->post_title }}</h2>
    <p>{{ $post->post_excerpt }}</p>
  @endforeach
@else
  <p>No posts found.</p>
@endif
```

**Rewrite — hook registration, narration trimmed, context kept:**

```php
// Before: Header block with unnecessary explanation
// Add a filter to modify the arguments passed to WP_Query when getting posts.
// This filter allows other parts of the code to customize the query behavior.
// The filter is attached at a late priority (20) to ensure it runs after the
// default Sage filters, so we can modify the arguments that other code has already set.
add_filter('sage/homepage_args', function ($args) {
  $args['orderby'] = 'meta_value_num';
  return $args;
}, 20);

// After: Only the non-obvious constraint
// Late priority (20) — runs after default Sage filters to modify their output
add_filter('sage/homepage_args', function ($args) {
  $args['orderby'] = 'meta_value_num';
  return $args;
}, 20);
```

**Keep — WordPress gotcha that could cause subtle bugs:**

```php
// Bad: Assuming the_loop has started; use get_posts() if you need raw data
// inside this filter before the loop runs (WP_Query sets global $post).
if (in_the_loop()) {
  the_content();
}

// Good: Comment explains why the check matters and when it would fail
// CRITICAL: the_loop must be running before this — get_post_meta() works
// but get_the_ID() returns 0 outside the loop, which breaks this query.
// Use get_posts() in pre-loop contexts instead.
if (in_the_loop()) {
  the_content();
}
```

**Keep — ACF/theme.json sync obligation:**

```php
// Keep in sync with theme.json spacing scale (theme.json → settings.spacing.spacingSizes)
$spacing_scale = [
  'xs' => '0.5rem',
  'sm' => '1rem',
  'md' => '1.5rem',
  'lg' => '2rem',
  'xl' => '3rem',
];
```

**Rewrite — Config comment, unnecessary padding removed:**

```php
// Before
'supports' => [
  // The post type supports the title field
  'title',
  // The post type supports the editor (body) field
  'editor',
  // Thumbnail is the featured image
  'thumbnail',
  // Custom fields using ACF
  'custom-fields',
],

// After
'supports' => [
  'title',
  'editor',
  'thumbnail',
  'custom-fields', // Enables ACF
],
```
