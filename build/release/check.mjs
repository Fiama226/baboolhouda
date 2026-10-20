/**
 * build/release/check.mjs
 * Integrity check for the cPanel release: everything the zip must contain is
 * verified BEFORE packaging. Exits non-zero on the first class of failure.
 *
 * Checks:
 *  - activation: mu-plugin auto-activation + 11 section page templates
 *  - data: quran.json (114 surahs / 6236 verses), names99 (99), adhkar,
 *    hadiths (>=40), prayer fallback (6 daily times)
 *  - built assets: theme dist (css/style.css, js/main.js, js/admin.js),
 *    plugin dist (js/admin.js, css/admin.css)
 *  - PWA icons (icon-512, favicon.svg)
 *  - scaffolding: wp-config-sample.php + .htaccess present,
 *    NO committed wp-config.php
 */
import { existsSync, readFileSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const site = path.join(root, 'site');
const theme = path.join(site, 'wp-content/themes/baaboul-houda');
const plugin = path.join(site, 'wp-content/plugins/baaboul-houda-core');

const failures = [];
function check(file, label) {
  const p = path.isAbsolute(file) ? file : path.join(root, file);
  if (!existsSync(p)) {
    failures.push(`${label} missing: ${path.relative(root, p)}`);
    return false;
  }
  return true;
}
function checkJson(file, label, test) {
  if (!check(file, label)) return;
  try {
    const data = JSON.parse(readFileSync(path.isAbsolute(file) ? file : path.join(root, file), 'utf8'));
    if (!test(data)) failures.push(`${label}: bad shape`);
  } catch (e) {
    failures.push(`${label}: unreadable (${e.message})`);
  }
}

// --- Activation -----------------------------------------------------------
const TEMPLATES = [
  'page-direct', 'page-radio', 'page-programmes', 'page-emissions', 'page-coran',
  'page-prieres', 'page-evenements', 'page-animateurs', 'page-contact',
  'page-actualites', 'page-apropos',
  // v2 section pages (activation.php)
  'page-outils', 'page-zakat', 'page-qibla', 'page-dates', 'page-tasbih',
  'page-wudu', 'page-adhkar', 'page-noms', 'page-mosquees', 'page-dons', 'page-publicite', 'page-temoignages',
  'page-messages', 'page-question', 'page-fatwas', 'page-galeries', 'page-podcast',
  'page-ramadan', 'page-enfants', 'page-presse', 'page-newsletter',
];
let templateCount = 0;
for (const t of TEMPLATES) {
  if (existsSync(path.join(theme, 'page-templates', `${t}.php`))) templateCount++;
}
if (templateCount !== TEMPLATES.length) {
  failures.push(`section templates: ${templateCount}/${TEMPLATES.length}`);
}
check(path.join(site, 'wp-content/mu-plugins/01-baaboul-houda-activate.php'), 'mu-plugin auto-activation');
check(path.join(plugin, 'includes/activation.php'), 'activation routine');

// --- Data -----------------------------------------------------------------
const data = (f) => path.join(plugin, 'data', f);
checkJson(data('quran.json'), 'quran.json', (d) =>
  Array.isArray(d.surahs) && d.surahs.length === 114 &&
  d.surahs.reduce((n, s) => n + (s.ayahs?.length || 0), 0) === 6236);
checkJson(data('names99.json'), 'names99.json', (d) =>
  Array.isArray(d.names) && d.names.length === 99);
checkJson(data('adhkar.json'), 'adhkar.json', (d) =>
  Array.isArray(d.sections) && d.sections.length >= 10);
checkJson(data('hadiths.json'), 'hadiths.json', (d) =>
  Array.isArray(d.hadiths) && d.hadiths.length >= 40);
checkJson(data('prayer-fallback.json'), 'prayer-fallback.json', (d) => {
  const months = d.months;
  if (!months || typeof months !== 'object') return false;
  const keys = ['fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha'];
  const n = Object.keys(months).length;
  if (n !== 12) return false;
  return Object.values(months).every((m) =>
    keys.every((k) => typeof m[k] === 'string' && /^\d{2}:\d{2}$/.test(m[k])));
});

// --- Built assets (committed for the cPanel zip) ---------------------------
check(path.join(theme, 'assets/dist/css/style.css'), 'theme style.css');
check(path.join(theme, 'assets/dist/js/main.js'), 'theme main.js');
check(path.join(theme, 'assets/dist/js/quran.js'), 'theme quran.js (lazy chunk)');
check(path.join(plugin, 'assets/dist/js/admin.js'), 'built admin.js');
check(path.join(plugin, 'assets/dist/css/admin.css'), 'built admin.css');
// CPT single templates (v2 community)
check(path.join(theme, 'single-fatwa.php'), 'single-fatwa template');
check(path.join(theme, 'single-galerie.php'), 'single-galerie template');

// --- PWA ------------------------------------------------------------------
check(path.join(theme, 'assets/icons/icon-512.png'), 'PWA icon-512');
check(path.join(theme, 'assets/icons/favicon.svg'), 'favicon.svg');

// --- Scaffolding ------------------------------------------------------------
check(path.join(site, 'wp-config-sample.php'), 'wp-config-sample.php');
check(path.join(site, '.htaccess'), '.htaccess');
if (existsSync(path.join(site, 'wp-config.php'))) {
  failures.push('wp-config.php must NOT be committed');
}

if (failures.length) {
  for (const f of failures) console.error(`  ✗ ${f}`);
  console.error(`release check: ${failures.length} failure(s)`);
  process.exit(1);
}
console.log(`release check OK — ${templateCount} templates, data + assets + scaffolding present`);
