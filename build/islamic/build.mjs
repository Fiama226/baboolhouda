/**
 * build/islamic/build.mjs
 * -----------------------
 * Builds the curated Islamic datasets bundled with the plugin:
 *  - data/adhkar.json   — Hisn al-Muslim (Le Château du Musulman), MIT
 *                         (github.com/YousefAsalya/Islamic-Pro-azkar-API):
 *                         Arabic text + counts, grouped by section.
 *  - data/hadiths.json  — Nawawi's Forty Hadith, Arabic text + narrator +
 *                         source (classical public-domain text; the English
 *                         translations of the upstream repo are © Salaf.Com
 *                         and are therefore NOT bundled).
 *  - data/prayer-fallback.json — approximate Ouagadougou prayer times
 *                         (offline fallback when the Aladhan API is down).
 *
 * Run `npm run build` after updating the sources in build/islamic/source/.
 */
import { readFileSync, writeFileSync, mkdirSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const srcDir = path.join(root, 'build/islamic/source');
const outDir = path.join(root, 'site/wp-content/plugins/baaboul-houda-core/data');
mkdirSync(outDir, { recursive: true });

/* ============================ ADHKAR ============================ */
const FR_CATEGORIES = {
  'أذكار الصباح والمساء': 'Adhkar du matin et du soir',
  'أذكار النوم': 'Adhkar du coucher',
  'أذكار الاستيقاظ من النوم': "Adhkar au réveil",
  'دعاء دخول الخلاء': 'Invocation pour entrer aux toilettes',
  'دعاء الخروج من الخلاء': 'Invocation pour sortir des toilettes',
  'الذكر قبل الوضوء': 'Dhikr avant l’ablution',
  'الذكر بعد الفراغ من الوضوء': 'Dhikr après l’ablution',
  'الذكر عند الخروج من المنزل': 'Dhikr en sortant de la maison',
  'الذكر عند دخول المنزل': 'Dhikr en entrant dans la maison',
  'دعاء الذهاب إلى المسجد': 'Invocation avant d’aller à la mosquée',
  'دعاء دخول المسجد': 'Invocation à l’entrée de la mosquée',
  'دعاء الخروج من المسجد': 'Invocation à la sortie de la mosquée',
  'أذكار الآذان': 'Adhkar à l’appel à la prière',
  'دعاء الاستفتاح': 'Invocation d’ouverture (salat)',
  'دعاء الركوع': 'Invocation du rukuʿ',
  'دعاء الرفع من الركوع': 'Invocation après le rukuʿ',
  'دعاء السجود': 'Invocation du sujūd',
  'دعاء الجلسة بين السجدتين': 'Invocation entre les deux sujūd',
  'دعاء سجود التلاوة': 'Invocation du sujūd al-tilāwa',
  'التشهد': 'Le Tashahhud',
  'الصلاة على النبي بعد التشهد': 'Salât sur le Prophète après le tashahhud',
  'الدعاء بعد التشهد الأخير قبل السلام': 'Invocation avant le salam final',
  'الأذكار بعد السلام من الصلاة': 'Adhkar après la prière',
  'دعاء صلاة الاستخارة': 'Invocation de l’istikhāra',
  'الدعاء إذا تقلب ليلا': 'Invocation en se réveillant la nuit',
  'دعاء الهم والحزن': 'Invocation contre l’inquiétude et la tristesse',
  'دعاء الكرب': 'Invocation dans l’épreuve',
  'ما يقول من خاف قوما': 'Invocation de celui qui craint un peuple',
  'دعاء قضاء الدين': 'Invocation contre les dettes',
  'دعاء الوسوسة في الصلاة و القراءة': 'Invocation contre les pensées parasites',
  'ما يقول ويفعل من أذنب ذنبا': "Quand on commet un péché",
  'دعاء طرد الشيطان و وساوسه': 'Invocation contre les suggestions du Diable',
  'ما يعوذ به الأولاد': "Protection des enfants",
  'الدعاء للمريض في عيادته': 'Invocation pour le malade',
  'دعاء المريض الذي يئس من حياته': 'Invocation du malade incurable',
  'دعاء التعزية': 'Invocation de condoléance',
  'دعاء زيارة القبور': 'Invocation lors de la visite des tombes',
  'دعاء الريح': 'Invocation pendant le vent',
  'دعاء الرعد': 'Invocation pendant le tonnerre',
  'الدعاء إذا نزل المطر': 'Invocation quand il pleut',
  'دعاء رؤية الهلال': 'Invocation à la vue du croissant',
  'الدعاء عند إفطار الصائم': 'Invocation de la rupture du jeûne',
  'الدعاء قبل الطعام': 'Invocation avant le repas',
  'الدعاء عند الفراغ من الطعام': 'Invocation après le repas',
  'دعاء العطاس': 'Invocation quand on éternue',
  'الدعاء للمتزوج': 'Invocation du marié',
  'دعاء الغضب': 'Invocation contre la colère',
  'دعاء من رأى مبتلى': 'Invocation pour voir un éprouvé',
  'دعاء الركوب': 'Invocation pour monter un véhicule',
  'دعاء السفر': 'Invocation du voyageur',
  'دعاء دخول القرية أو البلدة': 'Invocation à l’entrée d’une ville',
  'دعاء دخول السوق': 'Invocation à l’entrée du marché',
  'الذكر الرجوع من السفر': 'Dhikr du retour de voyage',
  'الاستغفار و التوبة': 'Demande de pardon et repentir',
  'فضل التسبيح و التحميد، و التهليل، و التكبير': 'Vertu du tesbīḥ, du tahmīd, du tahlīl, du takbīr',
  'كيف كان النبي يسبح؟': 'Comment le Prophète exaltait-il Allah ?',
  'من أنواع الخير والآداب الجامعة': 'Qualités et bonnes manières',
  'إفشاء السلام': 'Répandre le salām',
  'ما يقال في اﻟمجلس': "Ce qu'on dit dans une assemblée",
};

const adhkarRaw = JSON.parse(readFileSync(path.join(srcDir, 'adhkar/ar.json'), 'utf8'));
const adhkar = adhkarRaw.map((section) => ({
  id: section.id,
  title_ar: section.category,
  title_fr: FR_CATEGORIES[section.category] ?? section.category,
  items: section.array.map((it) => ({
    text: it.text,
    count: it.count ?? 1,
  })),
}));
writeFileSync(path.join(outDir, 'adhkar.json'), JSON.stringify({
  meta: {
    book: 'Hisn al-Muslim (Le Château du Musulman) — Shaikh Saʿīd al-Qahtānī',
    source: 'github.com/YousefAsalya/Islamic-Pro-azkar-API (MIT)',
    note_fr: 'Textes arabes avec les répétitions. Les sections sans traduction française affichent leur titre arabe.',
    note_ar: 'أذكار من كتاب حصن المسلم مع عدد التكرار.',
  },
  sections: adhkar,
}, null, 0));
console.log(`adhkar.json OK — ${adhkar.length} sections, ${adhkar.reduce((n, s) => n + s.items.length, 0)} items`);

/* ============================ HADITHS ============================ */
const hadiths = [];
for (let i = 1; i <= 42; i++) {
  const p = path.join(srcDir, `nawawi/hadith_${i}.json`);
  const h = JSON.parse(readFileSync(p, 'utf8'));
  hadiths.push({
    n: h.hadith_number,
    title: h.title,
    ar: h.arabic_text,
    narrator: h.narrator,
    source: h.source,
    audio: h.audio_url,
  });
}
if (hadiths.length !== 42) throw new Error(`Expected 42 hadiths, got ${hadiths.length}`);
writeFileSync(path.join(outDir, 'hadiths.json'), JSON.stringify({
  meta: {
    collection: 'Les quarante hadiths d’al-Nawawī (Al-Arbaʿīn al-Nawawiyya)',
    compiler: 'Imam al-Nawawī (631–676 h)',
    source: 'github.com/uthumany/nawawi-40-hadiths — textes arabes (domaine public). Les traductions de l’amont sont © Salaf.Com et ne sont pas redistribuées.',
    note_fr: 'Hadiths authentiques (les plus célèbres de la tradition). La traduction française pourra être ajoutée par l’équipe éditoriale.',
  },
  hadiths,
}, null, 0));
console.log(`hadiths.json OK — ${hadiths.length} hadiths`);

/* ============================ PRAYER FALLBACK ============================ */
// Approximate prayer times for Ouagadougou (12.37°N, 1.52°E), by month.
// Used only when the Aladhan API is unreachable; the UI labels them
// "horaires indicatifs" (indicative times).
const prayerFallback = {
  meta: {
    city: 'Ouagadougou',
    note_fr: 'Horaires indicatifs (approximatifs) utilisés hors-ligne. Les horaires exacts proviennent de l’API Aladhan.',
    note_ar: 'أوقات تقريبية تُستخدم دون اتصال.',
  },
  months: {
    1: { fajr: '05:55', sunrise: '06:45', dhuhr: '12:25', asr: '15:50', maghrib: '18:30', isha: '19:30' },
    2: { fajr: '05:45', sunrise: '06:30', dhuhr: '12:20', asr: '15:45', maghrib: '18:25', isha: '19:20' },
    3: { fajr: '05:30', sunrise: '06:15', dhuhr: '12:15', asr: '15:40', maghrib: '18:15', isha: '19:10' },
    4: { fajr: '05:20', sunrise: '06:00', dhuhr: '12:10', asr: '15:35', maghrib: '18:05', isha: '19:00' },
    5: { fajr: '05:15', sunrise: '05:50', dhuhr: '12:05', asr: '15:30', maghrib: '17:55', isha: '18:55' },
    6: { fajr: '05:10', sunrise: '05:45', dhuhr: '12:00', asr: '15:25', maghrib: '17:50', isha: '18:45' },
    7: { fajr: '05:10', sunrise: '05:45', dhuhr: '12:00', asr: '15:30', maghrib: '17:50', isha: '18:50' },
    8: { fajr: '05:15', sunrise: '05:50', dhuhr: '12:05', asr: '15:35', maghrib: '17:55', isha: '18:55' },
    9: { fajr: '05:20', sunrise: '06:00', dhuhr: '12:10', asr: '15:40', maghrib: '18:05', isha: '19:00' },
    10: { fajr: '05:30', sunrise: '06:10', dhuhr: '12:15', asr: '15:45', maghrib: '18:15', isha: '19:10' },
    11: { fajr: '05:40', sunrise: '06:25', dhuhr: '12:20', asr: '15:50', maghrib: '18:25', isha: '19:20' },
    12: { fajr: '05:50', sunrise: '06:40', dhuhr: '12:25', asr: '15:50', maghrib: '18:30', isha: '19:25' },
  },
};
writeFileSync(path.join(outDir, 'prayer-fallback.json'), JSON.stringify(prayerFallback, null, 0));
console.log('prayer-fallback.json OK');
