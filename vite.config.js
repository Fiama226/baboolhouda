import { defineConfig } from 'vite';
import { fileURLToPath } from 'node:url';

const theme = fileURLToPath(new URL('./site/wp-content/themes/baaboul-houda', import.meta.url));
const plugin = fileURLToPath(new URL('./site/wp-content/plugins/baaboul-houda-core', import.meta.url));

/**
 * All runtime JS/CSS is bundled here and committed to the repo, so the
 * cPanel release zip never needs Node.js on the host.
 */
export default defineConfig({
  build: {
    outDir: `${theme}/assets/dist`,
    emptyOutDir: true,
    minify: 'esbuild',
    target: 'es2019',
    rollupOptions: {
      input: {
        main: fileURLToPath(new URL('./site/wp-content/themes/baaboul-houda/assets-src/js/main.js', import.meta.url)),
        quran: fileURLToPath(new URL('./site/wp-content/themes/baaboul-houda/assets-src/js/quran.js', import.meta.url)),
        style: fileURLToPath(new URL('./site/wp-content/themes/baaboul-houda/assets-src/css/style.css', import.meta.url)),
        admin: fileURLToPath(new URL('./site/wp-content/plugins/baaboul-houda-core/assets-src/js/admin.js', import.meta.url)),
      },
      output: {
        // Stable, hash-free names: assets are versioned by ?ver= query string
        // (WP convention) and committed for the cPanel zip.
        entryFileNames: 'js/[name].js',
        chunkFileNames: 'js/[name].js',
        assetFileNames: (asset) => (asset.name?.endsWith('.css') ? 'css/[name][extname]' : 'assets/[name][extname]'),
      },
    },
  },
});
