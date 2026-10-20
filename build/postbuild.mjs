/**
 * build/postbuild.mjs
 * After `vite build` (single outDir = theme assets/dist), place the admin
 * bundle where the core plugin expects it (plugin assets/dist).
 */
import { copyFileSync, mkdirSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const from = path.join(root, 'site/wp-content/themes/baaboul-houda/assets/dist');
const to = path.join(root, 'site/wp-content/plugins/baaboul-houda-core/assets/dist');
mkdirSync(path.join(to, 'js'), { recursive: true });
mkdirSync(path.join(to, 'css'), { recursive: true });

copyFileSync(path.join(from, 'js/admin.js'), path.join(to, 'js/admin.js'));
copyFileSync(path.join(from, 'css/admin.css'), path.join(to, 'css/admin.css'));
console.log('postbuild: admin assets placed in plugin dist/');
