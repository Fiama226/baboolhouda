/**
 * build/quran/build.mjs
 * ---------------------
 * Transforms the raw Hafs-an-Asim (Uthmani script, Madinah mushaf) verse list
 * (build/quran/source/quran-hafs-uthmani.json, public domain) into the runtime
 * dataset shipped with the plugin:
 *   site/wp-content/plugins/baaboul-houda-core/data/quran.json
 *
 * French names / meanings and the commonly admitted Makki/Madani
 * classification are maintained in SURAH_META below (114 entries).
 */
import { readFileSync, writeFileSync, mkdirSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const SRC = path.join(root, 'build/quran/source/quran-hafs-uthmani.json');
const OUT = path.join(root, 'site/wp-content/plugins/baaboul-houda-core/data/quran.json');

/** [name_fr, meaning_fr, type] for surahs 1..114 — standard data. */
const SURAH_META = [
  ['Al-Fātiha', "L'Ouverture", 'mecquoise'],
  ['Al-Baqara', 'La Vache', 'medinoise'],
  ['Āl ʿImrān', 'La famille d’Imran', 'medinoise'],
  ['An-Nisāʾ', 'Les femmes', 'medinoise'],
  ['Al-Māʾida', 'La table servie', 'medinoise'],
  ['Al-Anʿām', 'Les bétails', 'mecquoise'],
  ['Al-Aʿrāf', 'Les hauteurs', 'mecquoise'],
  ['Al-Anfāl', 'Le butin de guerre', 'medinoise'],
  ['At-Tawba', 'Le repentir', 'medinoise'],
  ['Yūnus', 'Yunus', 'mecquoise'],
  ['Hūd', 'Hud', 'mecquoise'],
  ['Yūsuf', 'Yusuf', 'mecquoise'],
  ['Ar-Raʿd', 'Le tonnerre', 'medinoise'],
  ['Ibrāhīm', 'Ibrahim', 'mecquoise'],
  ['Al-Ḥijr', 'La roche fendue', 'mecquoise'],
  ['An-Naḥl', 'Le miel', 'mecquoise'],
  ['Al-Isrāʾ', "L'ascension nocturne", 'mecquoise'],
  ['Al-Kahf', 'La caverne', 'mecquoise'],
  ['Maryam', 'Marie', 'mecquoise'],
  ['Tā-Hā', 'Ta-Ha', 'mecquoise'],
  ['Al-Anbiyāʾ', 'Les prophètes', 'mecquoise'],
  ['Al-Ḥajj', 'Le pèlerinage', 'medinoise'],
  ['Al-Muʾminūn', 'Les croyants', 'mecquoise'],
  ['An-Nūr', 'La lumière', 'medinoise'],
  ['Al-Furqān', 'Le critère', 'mecquoise'],
  ['Ash-Shuʿarāʾ', 'Les poètes', 'mecquoise'],
  ['An-Naml', 'Les fourmis', 'mecquoise'],
  ['Al-Qaṣaṣ', 'Les récits', 'mecquoise'],
  ['Al-ʿAnkabūt', "L'araignée", 'mecquoise'],
  ['Ar-Rūm', 'Les Romains', 'mecquoise'],
  ['Luqmān', 'Luqman', 'mecquoise'],
  ['As-Sajda', 'La prosternation', 'mecquoise'],
  ['Al-Aḥzāb', 'Les coalisés', 'medinoise'],
  ['Sabaʾ', 'Le peuple de Saba', 'mecquoise'],
  ['Fāṭir', 'Le créateur', 'mecquoise'],
  ['Ya-Sīn', 'Ya-Sin', 'mecquoise'],
  ['Aṣ-Ṣāffāt', 'Les rangs', 'mecquoise'],
  ['Ṣād', 'Sad', 'mecquoise'],
  ['Az-Zumar', 'Les groupes', 'mecquoise'],
  ['Ghāfir', 'L’Excusable', 'mecquoise'],
  ['Fuṣṣilat', 'Les versets explicites', 'mecquoise'],
  ['Ash-Shūrā', 'La consultation', 'mecquoise'],
  ['Az-Zukhruf', "L'or", 'mecquoise'],
  ['Ad-Dukhān', 'La fumée', 'mecquoise'],
  ['Al-Jāthiya', 'Genou plié', 'mecquoise'],
  ['Al-Aḥqāf', 'Les dunes de sable', 'mecquoise'],
  ['Muḥammad', 'Muhammad', 'medinoise'],
  ['Al-Fatḥ', 'La victoire', 'medinoise'],
  ['Al-Ḥujurāt', 'Les pièces du château', 'medinoise'],
  ['Qāf', 'Qaf', 'mecquoise'],
  ['Adh-Dhāriyāt', 'Les vents qui balayent', 'mecquoise'],
  ['Aṭ-Ṭūr', 'La montagne', 'mecquoise'],
  ['An-Najm', "L'étoile", 'mecquoise'],
  ['Al-Qamar', 'La lune', 'mecquoise'],
  ['Ar-Raḥmān', 'Le Tout-Miséricordieux', 'medinoise'],
  ['Al-Wāqiʿa', "L'événement", 'mecquoise'],
  ['Al-Ḥadīd', 'Le fer', 'medinoise'],
  ['Al-Mujādila', 'La querelle', 'medinoise'],
  ['Al-Ḥashr', "L'arrachement", 'medinoise'],
  ['Al-Mumtaḥana', 'La femme mise à l’épreuve', 'medinoise'],
  ['Aṣ-Ṣaff', 'La rangée', 'medinoise'],
  ['Al-Jumuʿa', 'Le vendredi', 'medinoise'],
  ['Al-Munāfiqūn', 'Les hypocrites', 'medinoise'],
  ['At-Taghābun', 'La journée du bilan', 'medinoise'],
  ['Aṭ-Ṭalāq', 'Le divorce', 'medinoise'],
  ['At-Taḥrīm', "L'interdiction", 'medinoise'],
  ['Al-Mulk', 'Le pouvoir', 'mecquoise'],
  ['Al-Qalam', 'Le calame', 'mecquoise'],
  ['Al-Ḥāqqa', 'La réalité', 'mecquoise'],
  ['Al-Maʿārij', 'Les échelons', 'mecquoise'],
  ['Nūḥ', 'Nuh', 'mecquoise'],
  ['Al-Jinn', 'Les djinns', 'mecquoise'],
  ['Al-Muzzammil', "L'enveloppé", 'mecquoise'],
  ['Al-Muddaththir', "L'invité à se couvrir", 'mecquoise'],
  ['Al-Qiyāma', 'La résurrection', 'mecquoise'],
  ['Al-Insān', 'L’homme', 'medinoise'],
  ['Al-Mursalāt', 'Les envoyées', 'mecquoise'],
  ['An-Nabaʾ', 'La nouvelle', 'mecquoise'],
  ['An-Nāziʿāt', 'Celles qui arrachent', 'mecquoise'],
  ['ʿAbasa', 'Il a froncé les sourcils', 'mecquoise'],
  ['At-Takwīr', "L'enroulement", 'mecquoise'],
  ['Al-Infiṭār', 'La fente', 'mecquoise'],
  ['Al-Muṭaffifīn', 'Les fraudeurs', 'mecquoise'],
  ['Al-Inshiqāq', 'La déchirure', 'mecquoise'],
  ['Al-Burūj', 'Les astres', 'mecquoise'],
  ['Aṭ-Ṭāriq', "L'étoile du soir", 'mecquoise'],
  ['Al-Aʿlā', 'Le Très-Haut', 'mecquoise'],
  ['Al-Ghāshiya', 'La nuit obscurcie', 'mecquoise'],
  ['Al-Fajr', "L'aube", 'mecquoise'],
  ['Al-Balad', 'La ville', 'mecquoise'],
  ['Ash-Shams', 'Le soleil', 'mecquoise'],
  ['Al-Layl', 'La nuit', 'mecquoise'],
  ['Ad-Duḥā', 'Le matin', 'mecquoise'],
  ['Ash-Sharḥ', 'L’élargissement', 'mecquoise'],
  ['At-Tīn', 'Le figuier', 'mecquoise'],
  ['Al-ʿAlaq', "L'agrafe", 'mecquoise'],
  ['Al-Qadr', "La nuit du destin", 'mecquoise'],
  ['Al-Bayyina', 'La preuve', 'medinoise'],
  ['Az-Zalzala', 'Le tremblement de terre', 'medinoise'],
  ['Al-Ādiyāt', 'Les charges', 'mecquoise'],
  ['Al-Qāriʿa', 'Le fracas', 'mecquoise'],
  ['At-Takāthur', 'La rivalité', 'mecquoise'],
  ['Al-ʿAṣr', 'Le siècle', 'mecquoise'],
  ['Al-Humāza', 'Le calomniateur', 'mecquoise'],
  ['Al-Fīl', "L'éléphant", 'mecquoise'],
  ['Quraysh', 'Quraysh', 'mecquoise'],
  ['Al-Māʿūn', 'Le petit service', 'mecquoise'],
  ['Al-Kawthar', 'L’abondance', 'mecquoise'],
  ['Al-Kāfirūn', 'Les mécroyants', 'mecquoise'],
  ['An-Naṣr', 'Le secours', 'medinoise'],
  ['Al-Masad', 'Le feu de paille', 'mecquoise'],
  ['Al-Ikhlāṣ', 'Le monothéisme', 'mecquoise'],
  ['Al-Falaq', "L'aube naissante", 'mecquoise'],
  ['An-Nās', 'Les gens', 'mecquoise'],
];

const raw = JSON.parse(readFileSync(SRC, 'utf8'));
if (raw.verse_count !== 6236 || raw.verses.length !== 6236) {
  throw new Error(`Unexpected verse count: ${raw.verse_count}/${raw.verses.length}`);
}

const surahs = [];
let globalAyah = 0;
for (const v of raw.verses) {
  const s = v.verse_key.split(':')[0];
  if (!surahs.length || surahs[surahs.length - 1].n !== Number(s)) {
    const [nameFr, meaningFr, type] = SURAH_META[s - 1];
    if (!nameFr) throw new Error(`Missing meta for surah ${s}`);
    surahs.push({ n: Number(s), name_fr: nameFr, meaning_fr: meaningFr, type, ayahs: [], pages: [v.page_number, v.page_number], juz: new Set([v.juz_number]) });
  }
  globalAyah += 1;
  const sur = surahs[surahs.length - 1];
  sur.pages[1] = v.page_number;
  sur.juz.add(v.juz_number);
  sur.ayahs.push({ g: globalAyah, v: v.verse_number, text: v.text_uthmani.trim() });
}
if (surahs.length !== 114) throw new Error(`Expected 114 surahs, got ${surahs.length}`);

const out = {
  meta: {
    riwaya: raw.riwaya,
    mushaf: raw.mushaf,
    source: raw.source,
    generated: new Date().toISOString(),
    verse_count: 6236,
    note_fr: 'Texte du Noble Coran, riyāda Hafs ʿan ʿĀṣim, moucharaf de Médine (texte public domain, Tanzil/QCF). Traductions françaises disponibles en ligne (alquran.cloud, CC-BY).',
    note_ar: 'نص القرآن الكريم برواية حفص عن عاصم (المصحف المديني).',
  },
  surahs: surahs.map((s) => ({
    n: s.n,
    name_ar: arName(s.n),
    name_fr: s.name_fr,
    meaning_fr: s.meaning_fr,
    type: s.type,
    ayah_count: s.ayahs.length,
    pages: s.pages,
    juz: [...s.juz].sort((a, b) => a - b),
    start_global: s.ayahs[0].g,
    ayahs: s.ayahs.map((a) => a.text),
  })),
};

// The 114 Arabic surah names (Uthmani spelling), canonical list.
function arName(n) {
  return [
    'الفاتحة', 'البقرة', 'آل عمران', 'النساء', 'المائدة', 'الأنعام', 'الأعراف', 'الأنفال', 'التوبة',
    'يونس', 'هود', 'يوسف', 'الرعد', 'إبراهيم', 'الحجر', 'النحل', 'الإسراء', 'الكهف', 'مريم',
    'طه', 'الأنبياء', 'الحج', 'المؤمنون', 'النور', 'الفرقان', 'الشعراء', 'النمل', 'القصص', 'العنكبوت',
    'الروم', 'لقمان', 'السجدة', 'الأحزاب', 'سبأ', 'فاطر', 'يس', 'الصافات', 'ص',
    'الزمر', 'غافر', 'فصلت', 'الشورى', 'الزخرف', 'الدخان', 'الجاثية', 'الأحقاف', 'محمد',
    'الفتح', 'الحجرات', 'ق', 'الذاريات', 'الطور', 'النجم', 'القمر', 'الرحمن', 'الواقعة',
    'الحديد', 'المجادلة', 'الحشر', 'الممتحنة', 'الصف', 'الجمعة', 'المنافقون', 'التغابن', 'الطلاق',
    'التحريم', 'الملك', 'القلم', 'الحاقة', 'المعارج', 'نوح', 'الجن', 'المزمل', 'المدثر',
    'القيامة', 'الإنسان', 'المرسلات', 'النبأ', 'النازعات', 'عبس', 'التكوير', 'الانفطار', 'المطففين',
    'الانشقاق', 'البروج', 'الطارق', 'الأعلى', 'الغاشية', 'الفجر', 'البلد', 'الشمس', 'الليل',
    'الضحى', 'الشرح', 'التين', 'العلق', 'القدر', 'البينة', 'الزلزلة', 'العاديات', 'القارعة',
    'التكاثر', 'العصر', 'الهمزة', 'الفيل', 'قريش', 'الماعون', 'الكوثر', 'الكافرون', 'النصر',
    'المسد', 'الإخلاص', 'الفلق', 'الناس',
  ][n - 1];
}

mkdirSync(path.dirname(OUT), { recursive: true });
writeFileSync(OUT, JSON.stringify(out, null, 0));
console.log(`quran.json written: ${surahs.length} surahs, ${globalAyah} verses, ${(out.ayahs_total ?? 0)}`);
const kb = (JSON.stringify(out).length / 1024).toFixed(0);
console.log(`size: ${kb} KB (minified)`);
