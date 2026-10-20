/**
 * build/quran/validate.mjs — integrity checks on the bundled Quran dataset.
 * Run by `npm test` and by CI. Fails hard on any inconsistency.
 */
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const OUT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../../site/wp-content/plugins/baaboul-houda-core/data/quran.json');
const d = JSON.parse(readFileSync(OUT, 'utf8'));

let total = 0;
let failures = 0;
const fail = (msg) => { failures += 1; console.error(`  ✗ ${msg}`); };

if (d.surahs.length !== 114) fail(`114 surahs expected, got ${d.surahs.length}`);
if (d.meta.verse_count !== 6236) fail(`meta.verse_count is ${d.meta.verse_count}`);

let prevEnd = 0;
for (const s of d.surahs) {
  if (s.n !== d.surahs.indexOf(s) + 1) fail(`surah order broken at ${s.n}`);
  if (!s.name_ar || !s.name_fr || !s.meaning_fr) fail(`surah ${s.n}: missing names`);
  if (!s.type || !['mecquoise', 'medinoise'].includes(s.type)) fail(`surah ${s.n}: bad type ${s.type}`);
  if (s.ayah_count !== s.ayahs.length) fail(`surah ${s.n}: ayah_count mismatch`);
  if (s.ayahs.some((a) => typeof a !== 'string' || !a.trim())) fail(`surah ${s.n}: empty ayah`);
  if (s.start_global !== prevEnd + 1) fail(`surah ${s.n}: global index gap`);
  if (!Array.isArray(s.juz) || !s.juz.length) fail(`surah ${s.n}: missing juz`);
  if (s.pages[0] > s.pages[1]) fail(`surah ${s.n}: bad page range`);
  prevEnd = s.start_global + s.ayahs.length - 1;
  total += s.ayahs.length;
}
if (total !== 6236) fail(`total verses ${total} != 6236`);

// Spot checks — verify the consonantal skeleton (radical letters) of well-known
// verses, immune to Uthmani hamza/diacritic encoding variants.
const skeleton = (x) =>
  x
    .replace(/[\u0610-\u061A\u064B-\u065F\u0670\u06D6-\u06ED\u08F0-\u08FF\u0640]/g, '')
    .replace(/[أإآٱٲٳ]/g, 'ا');
const spot = {
  '1:1': ['بسم', 'الرحيم'],
  '2:255': ['الله لا', 'العظيم'],
  '112:1': ['قل هو', 'احد'],
  '114:6': ['من', 'الناس'],
};
for (const [key, [prefix, suffix]] of Object.entries(spot)) {
  const [sn, vn] = key.split(':').map(Number);
  const got = skeleton(d.surahs[sn - 1].ayahs[vn - 1]);
  if (!got.startsWith(skeleton(prefix)) || !got.endsWith(skeleton(suffix))) fail(`spot check ${key}: unexpected text "${got.slice(0, 60)}…"`);
}

if (failures) {
  console.error(`quran.json: ${failures} failure(s)`);
  process.exit(1);
}
console.log(`quran.json OK — 114 surahs, ${total} verses`);
