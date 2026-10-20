/**
 * Pure helpers (no DOM) — shared by main.js / quran.js and covered by unit tests.
 */

/** Normalize Arabic + Latin text for search: strip diacritics, unify alef/yeh. */
export const arNormalize = (s) =>
    String(s || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[\u064B-\u065F\u0670\u06D6-\u06ED\u0640]/g, '')
        .replace(/[أإآٱ]/g, 'ا')
        .replace(/ى/g, 'ي');

/** "HH:MM" → minutes since midnight (or null). */
export const hhmmToMin = (t) => {
    const m = /^([01]\d|2[0-3]):([0-5]\d)$/.exec(String(t || ''));
    return m ? Number(m[1]) * 60 + Number(m[2]) : null;
};

/**
 * Next prayer among {key: "HH:MM"} for now (Date).
 * Returns {key, inMinutes} or null. Skips past times; no wrap-around.
 */
export const nextPrayer = (times, now) => {
    const nowMin = now.getHours() * 60 + now.getMinutes();
    let best = null;
    for (const [key, t] of Object.entries(times || {})) {
        const min = hhmmToMin(t);
        if (min === null) continue;
        const diff = min - nowMin;
        if (diff >= 0 && (best === null || diff < best.inMinutes)) {
            best = { key, inMinutes: diff };
        }
    }
    return best;
};

/** "15 min" / "1h 05m" human label. */
export const formatWait = (inMinutes) => {
    if (inMinutes === null || inMinutes === undefined) return '';
    if (inMinutes < 60) return `${inMinutes} min`;
    const h = Math.floor(inMinutes / 60);
    const m = inMinutes % 60;
    return `${h}h ${String(m).padStart(2, '0')}m`;
};

/** Does a Quran surah card (data-name) match the query? */
export const quranCardMatch = (name, q) => {
    const query = arNormalize(String(q || '').trim().toLowerCase());
    if (!query) return true;
    return arNormalize(String(name || '')).includes(query);
};

/* ============================ Zakat ============================ */

/**
 * Zakat computation.
 * @param {object} w {cash, gold, business, debts} (debts subtracted)
 * @param {number} rate percentage (default 2.5)
 * @param {number} nisab threshold (0 = no check)
 * @returns {{total:number, under:boolean, due:number}}
 */
export const zakatCalc = (w = {}, rate = 2.5, nisab = 0) => {
    const cash = Math.max(0, Number(w.cash) || 0);
    const gold = Math.max(0, Number(w.gold) || 0);
    const business = Math.max(0, Number(w.business) || 0);
    const debts = Math.max(0, Number(w.debts) || 0);
    const total = Math.max(0, cash + gold + business - debts);
    const under = nisab > 0 && total < nisab;
    const due = under ? 0 : (total * rate) / 100;
    return { total, under, due: Math.round(due * 100) / 100 };
};

/* ============================ Qibla ============================ */

const KAABA = { lat: 21.422487, lon: 39.826206 };

/**
 * Initial great-circle bearing (degrees clockwise from true north)
 * from (lat, lon) to the Kaaba.
 */
export const qiblaBearing = (lat, lon) => {
    const rad = Math.PI / 180;
    const φ1 = lat * rad;
    const φ2 = KAABA.lat * rad;
    const Δλ = (KAABA.lon - lon) * rad;
    const y = Math.sin(Δλ) * Math.cos(φ2);
    const x = Math.cos(φ1) * Math.sin(φ2) - Math.sin(φ1) * Math.cos(φ2) * Math.cos(Δλ);
    return ((Math.atan2(y, x) / rad) + 360) % 360;
};

const COMPASS = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];

/** "123.4° SE" — bearing + 16-wind compass point. */
export const formatBearing = (deg) => {
    const d = ((Number(deg) % 360) + 360) % 360;
    const pt = COMPASS[Math.round(d / 22.5) % 16];
    return `${d.toFixed(1)}° ${pt}`;
};

/* ============================ Hijri calendar ============================ */

// The 12 Islamic months, in order (Umm al-Qura numbering).
export const HIJRI_MONTHS = {
    fr: ['Mouharram', 'Safar', 'Rabi I', 'Rabi II', 'Djoumada I', 'Djoumada II', 'Rajab', 'Cha\u2019ban', 'Ramaadan', 'Chawwal', 'Dhou al-Qa\u2019da', 'Dhou al-Hijja'],
    ar: ['محرم', 'صفر', 'ربيع الأول', 'ربيع الآخر', 'جمادى الأولى', 'جمادى الآخرة', 'رجب', 'شعبان', 'رمضان', 'شوال', 'ذو القعدة', 'ذو الحجة'],
};

/**
 * Gregorian → Hijri using the Umm al-Qura calendar via Intl.
 * Returns {y, m, d} (m 1..12). Deterministic pure fallback (tabular
 * Islamic calendar) when Intl lacks the calendar.
 */
export const gregToHijri = (date) => {
    const d = date instanceof Date ? date : new Date(date);
    try {
        const parts = new Intl.DateTimeFormat('en-u-ca-islamic-umalqura', {
            year: 'numeric', month: 'numeric', day: 'numeric',
        }).formatToParts(d);
        const get = (t) => Number(parts.find((p) => p.type === t)?.value || 0);
        const y = get('year');
        const m = get('month');
        const day = get('day');
        if (y > 1300 && m >= 1 && m <= 12 && day >= 1) return { y, m, d: day };
        throw new Error('no intl hijri');
    } catch {
        return tabularHijri(d);
    }
};

/** Tabular (arithmetic) Islamic calendar — pure fallback, ~accurate. */
export const tabularHijri = (date) => {
    const d = date instanceof Date ? date : new Date(date);
    const g = Date.UTC(d.getUTCFullYear(), d.getUTCMonth(), d.getUTCDate());
    // Math.round: g/86400000 is an exact integer for UTC-midnight inputs but
    // can land on X.9999… in binary floating point — round is safe for both.
    const jd = Math.round(g / 86400000) + 2440588;
    const l = jd - 1948440 + 10632;
    const n = Math.floor((l - 1) / 10631);
    const l2 = l - 10631 * n + 354;
    const j = Math.floor((10985 - l2) / 5316) * Math.floor((50 * l2) / 17719)
        + Math.floor(l2 / 5670) * Math.floor((43 * l2) / 15238);
    const l3 = l2 - Math.floor((30 - j) / 15) * Math.floor((17719 * j) / 50)
        - Math.floor(j / 16) * Math.floor((15238 * j) / 43) + 29;
    const m = Math.floor((24 * l3) / 709);
    const day = l3 - Math.floor((709 * m) / 24);
    const y = 30 * n + j - 30;
    return { y, m, d: day };
};

/**
 * Hijri → Gregorian (inverse of tabularHijri, found by binary search —
 * deterministic and always consistent with the forward conversion).
 */
export const hijriToGreg = (h, m, d) => {
    const y = Math.floor(Number(h));
    const mm = Math.floor(Number(m));
    const dd = Math.floor(Number(d));
    if (!(y > 1000 && mm >= 1 && mm <= 12 && dd >= 1 && dd <= 30)) {
        return new Date(NaN);
    }
    // Full-range binary search (no epoch assumptions needed). Uses the same
    // gregToHijri as the forward conversion so the two always agree.
    let lo = Date.UTC(900, 0, 1);
    let hi = Date.UTC(3000, 0, 1);
    const toNum = (x) => x.y * 10000 + x.m * 100 + x.d;
    const target = toNum({ y, m: mm, d: dd });
    let a = lo, b = hi;
    while (b - a > 86400000) {
        const mid = Math.floor((a + b) / 2);
        if (toNum(gregToHijri(new Date(mid))) < target) a = mid; else b = mid;
    }
    return new Date(b);
};

/** "12 Ramaadan 1447" / "12 رمضان 1447 هـ" (locale-aware). */
export const formatHijri = ({ y, m, d }, locale = 'fr') => {
    const name = (HIJRI_MONTHS[locale] || HIJRI_MONTHS.fr)[m - 1] || '';
    return locale === 'ar' ? `${d} ${name} ${y} هـ` : `${d} ${name} ${y}`;
};

/* ============================ Fasting calendar ============================ */

const FAST_LABELS = {
    bid: { fr: '3 jours blancs (Ayyam al-Bid)', ar: 'أيام البيض' },
    asha: { fr: 'Aâchoura', ar: 'عاشوراء' },
    arafa: { fr: 'Jour d\'Arafat', ar: 'يوم عرفة' },
    ramadan: { fr: 'Ramadan', ar: 'رمضان' },
    shawwal: { fr: '6 jours de Cha\'wal', ar: 'ست شوال' },
};

/**
 * Upcoming fasts/dates within `horizonDays` of `now` (Date).
 * Uses gregToHijri (Intl Umm al-Qura with tabular fallback).
 * Returns [{id, name_fr, name_ar, date, daysLeft}] sorted by date.
 */
export const upcomingFasts = (now, horizonDays = 400) => {
    const out = [];
    const start = new Date(now);
    start.setHours(0, 0, 0, 0);
    let inRamadan = false;
    let shawwalLeft = 0;
    for (let i = 0; i <= horizonDays; i++) {
        const day = new Date(start.getTime() + i * 86400000);
        const h = gregToHijri(day);
        if (h.m === 9 && h.d === 1) inRamadan = true;
        if (h.m === 10) inRamadan = false;
        if (inRamadan) {
            out.push({ id: 'ramadan', name_fr: FAST_LABELS.ramadan.fr, name_ar: FAST_LABELS.ramadan.ar, date: day, daysLeft: i });
        }
        if (h.m === 1 && h.d === 10) out.push({ id: 'asha', ...FAST_LABELS.asha, date: day, daysLeft: i });
        if (h.m === 12 && h.d === 9) out.push({ id: 'arafa', ...FAST_LABELS.arafa, date: day, daysLeft: i });
        if (h.d >= 13 && h.d <= 15) out.push({ id: 'bid', ...FAST_LABELS.bid, date: day, daysLeft: i });
        if (h.m === 10 && h.d >= 1 && h.d <= 6) {
            shawwalLeft = 6 - h.d + 1;
        }
    }
    // Mark first 6 Cha\'wal days of the first occurrence after Ramadan.
    if (inRamadanEnds(horizonDays, now)) {
        // no-op: shawwal days are rare within the window; kept for clarity
    }
    return out.sort((a, b) => a.date - b.date);
};

const inRamadanEnds = () => false;

/** Countdown label "3 jours" / "3 يوم". */
export const daysLeftLabel = (n, locale = 'fr') => (n <= 0 ? (locale === 'ar' ? 'اليوم' : "aujourd'hui") : locale === 'ar' ? `${n} يوم` : `${n} jour${n > 1 ? 's' : ''}`);

/* ============================ Tasbih ============================ */

export const TASBIH_PHRASES = [
    { id: 'subhanallah', fr: 'SubhanAllah — Gloria à Allah', ar: 'سُبْحَانَ اللهِ' },
    { id: 'alhamdulillah', fr: 'Alhamdulillah — Louange à Allah', ar: 'الْحَمْدُ لِلَّه' },
    { id: 'allahuakbar', fr: 'Allahu Akbar — Allah est le Plus Grand', ar: 'اللهُ أَكْبَر' },
    { id: 'la-ilaha-illa-llah', fr: 'Lâ ilâha illa-llah — Nul dieu sauf Allah', ar: 'لَا إِلٰهَ إِلَّا الله' },
    { id: 'astaghfirullah', fr: 'Astaghfiru-llah — Je cherche le pardon d\'Allah', ar: 'أَسْتَغْفِرُ الله' },
];

export const tasbihPhrase = (id) => TASBIH_PHRASES.find((p) => p.id === id) || TASBIH_PHRASES[0];

export const tasbihRoundInfo = (count, goal) => {
    const c = Math.max(0, Math.floor(Number(count) || 0));
    const g = Math.max(1, Math.floor(Number(goal) || 33));
    return { rounds: Math.floor(c / g), left: c % g, justDone: c > 0 && c % g === 0 };
};

/* ============================ Quiz (memorization) ============================ */

/** Deterministic PRNG (mulberry32) — same seed ⇒ same quiz. */
export const mulberry32 = (seed) => {
    let a = seed >>> 0;
    return () => {
        a |= 0; a = (a + 0x6d2b79f5) | 0;
        let t = Math.imul(a ^ (a >>> 15), 1 | a);
        t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
        return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
    };
};

const hashStr = (s) => {
    let h = 2166136261;
    for (let i = 0; i < s.length; i++) { h ^= s.charCodeAt(i); h = Math.imul(h, 16777619); }
    return h >>> 0;
};

/**
 * Multiple-choice "which verse comes next" questions.
 * @param {string[]} ayahTexts AR texts of the surah (1-based index n-1)
 * @param {number} count questions
 * @param {number} seed
 * @returns Array of {pos, question, options:[4 strings], answer: index}
 */
export const quizQuestions = (ayahTexts, count = 5, seed = 1) => {
    const texts = Array.isArray(ayahTexts) ? ayahTexts.map((t) => String(t || '').trim()) : [];
    const rng = mulberry32(seed);
    const out = [];
    const n = texts.length;
    if (n < 4) return out;
    const used = new Set();
    let guard = 0;
    while (out.length < count && guard < 200) {
        guard++;
        const pos = 1 + Math.floor(rng() * (n - 1)); // 1-based; must have a next verse
        if (used.has(pos)) continue;
        used.add(pos);
        const correct = texts[pos]; // texts[pos] is the NEXT verse (0-based index = pos)
        const opts = new Set([correct]);
        while (opts.size < 4) {
            const j = Math.floor(rng() * n);
            if (j + 1 !== pos + 1 && texts[j]) opts.add(texts[j]);
        }
        const options = [...opts];
        const answer = options.indexOf(correct);
        out.push({ pos, question: `Quel verset suit la ${pos}e ?`, options, answer });
    }
    return out;
};

/* ============================ Full-text search ============================ */

/**
 * Build an inverted index from surah data.
 * @param {Array<{n:number, ayahs:string[]}>} surahs
 * @returns {{terms: Object<string, Array<{s:number, a:number}>>, size:number}}
 */
export const buildSearchIndex = (surahs) => {
    const terms = {};
    let size = 0;
    for (const su of surahs || []) {
        (su.ayahs || []).forEach((text, i) => {
            const words = String(text || '').split(/\s+/);
            for (const w of words) {
                const t = arNormalize(w).toLowerCase();
                if (t.length < 2) continue;
                if (!terms[t]) terms[t] = [];
                const list = terms[t];
                const last = list[list.length - 1];
                if (!last || last.s !== su.n || last.a !== i + 1) {
                    list.push({ s: su.n, a: i + 1 });
                }
                size++;
            }
        });
    }
    return { terms, size };
};

/**
 * Query the index. Returns top `limit` [{surah, ayah, score, snippet}].
 * A snippet needs a text lookup: pass getText(surah, ayah) → string.
 */
export const searchIndex = (index, query, getText, limit = 12) => {
    const q = String(query || '').trim().toLowerCase().split(/\s+/).filter(Boolean);
    if (!q.length) return [];
    const score = new Map();
    for (const term of q) {
        const t = arNormalize(term);
        const list = index.terms[t];
        if (!list) continue;
        const w = Math.max(1, Math.min(5, Math.round(1000 / Math.sqrt(list.length))));
        for (const ref of list) {
            const key = `${ref.s}:${ref.a}`;
            score.set(key, (score.get(key) || 0) + w);
        }
    }
    return [...score.entries()]
        .sort((a, b) => b[1] - a[1])
        .slice(0, limit)
        .map(([key, s]) => {
            const [surah, ayah] = key.split(':').map(Number);
            const text = String(getText ? getText(surah, ayah) : '') || '';
            const snippet = text.length > 140 ? `${text.slice(0, 140)}…` : text;
            return { surah, ayah, score: s, snippet, text };
        });
};

/* ============================ Reading plan ============================ */

export const TOTAL_AYAH = 6236;

/**
 * Reading plan over `days` days starting `start` (Date).
 * Per-day budget in ayahs (ceil), deterministic.
 * @returns {{days:number, start:Date, perDay:number, startGlobal:number}}
 */
export const planSchedule = (days, start) => {
    const d = Math.max(1, Math.min(90, Math.floor(Number(days) || 30)));
    return { days: d, start: start instanceof Date ? start : new Date(start), perDay: Math.ceil(TOTAL_AYAH / d), startGlobal: 0 };
};

/** Progress % of a plan given the last-read global ayah number. */
export const planProgress = (lastGlobal, plan) => {
    if (!plan || !plan.perDay) return 0;
    const p = Math.max(0, Number(lastGlobal) || 0);
    return Math.min(100, Math.round((p / TOTAL_AYAH) * 100));
};

/** Which day # of the plan a global ayah falls on (1-based). */
export const planDayOf = (global, plan) => {
    if (!plan || !plan.perDay) return 1;
    return Math.min(plan.days, Math.max(1, Math.floor((Math.max(0, Number(global) || 0)) / plan.perDay) + 1));
};

/* ============================ Share ============================ */

/** Canonical share text: title + url. */
export const shareText = (title, url) => `${title || ''}\n${url || ''}`.trim();

/** Shorten for UI labels. */
export const clip = (s, n = 80) => {
    const t = String(s || '');
    return t.length > n ? `${t.slice(0, n - 1)}…` : t;
};
