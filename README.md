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

# [ACF Composer](https://github.com/log1x/acf-composer)

Provides an artisan-like CLI for managing ACF fields and blocks in a WordPress project. Checkout the link above for example usage and documentation. See the below example.

## [TextHero Block](site/web/app/themes/sage/app/Blocks/TextHero.php)

Blade file: [site/web/app/themes/sage/resources/views/blocks/text-hero.blade.php](site/web/app/themes/sage/resources/views/blocks/text-hero.blade.php)

This block uses the core/heading and core/paragraph blocks as content. The block is registered in the `TextHero.php` file and the fields are defined in the `fields()` method. The block is rendered in the `with()` method, which passes the block data to the Blade template. The `template` property defines the default inner blocks for the block.
