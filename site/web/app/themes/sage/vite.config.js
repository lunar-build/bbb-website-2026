import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { wordpressPlugin, wordpressThemeJson } from '@roots/vite-plugin';

// Set APP_URL if it doesn't exist for Laravel Vite plugin
if (!process.env.APP_URL) {
  process.env.APP_URL = 'https://betterbybike.test';
}

export default defineConfig({
  base: '/app/themes/sage/public/build/',
  plugins: [
    laravel({
      input: [
        'resources/js/app.js',
        'resources/css/editor.scss',
        'resources/js/editor.js',
        'resources/js/editor-canvas.js',
        'resources/js/admin/term-timetable/index.jsx',
      ],
      refresh: true,
      assets: [
        'resources/images/**',
        'resources/fonts/**',
        'resources/videos/**',
      ],
    }),

    wordpressPlugin(),

    // Generate the theme.json file in the public/build/assets directory
    // from the hand-authored theme.json in the base theme folder
    wordpressThemeJson({
      disableTailwindColors: true,
      disableTailwindFonts: true,
      disableTailwindFontSizes: true,
      disableTailwindBorderRadius: true,
    }),
  ],
  resolve: {
    alias: {
      // Third-party React code (@dnd-kit) must share WordPress's React, not bundle its own.
      // Absolute paths: the dev server's dependency pre-bundler doesn't resolve root-relative ones.
      react: fileURLToPath(new URL('./resources/js/admin/shims/react.js', import.meta.url)),
      'react-dom': fileURLToPath(new URL('./resources/js/admin/shims/react-dom.js', import.meta.url)),
      '@scripts': '/resources/js',
      '@styles': '/resources/css',
      '@fonts': '/resources/fonts',
      '@images': '/resources/images',
    },
  },
  server: {
    // Respond to all network requests
    host: '0.0.0.0',
    port: 5173,
    strictPort: true,
    // Defines the origin of the generated asset URLs during development,
    // this must be set to the Vite dev server URL and selected port.
    origin: `${process.env.DDEV_PRIMARY_URL_WITHOUT_PORT}:5173`,
    // Configure CORS securely for the Vite dev server to allow requests
    // from *.ddev.site domains, supports additional hostnames (via regex).
    // If you use another `project_tld`, adjust this value accordingly.
    cors: {
      origin: /https?:\/\/([A-Za-z0-9\-\.]+)?(\.test)(?::\d+)?$/,
    },
  },
});
