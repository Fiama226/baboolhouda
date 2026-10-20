/**
 * Pure helpers (no DOM) — shared by main.js and covered by unit tests.
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
