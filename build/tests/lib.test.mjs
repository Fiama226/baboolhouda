import { describe, it, expect } from 'vitest';
import {
    arNormalize, hhmmToMin, nextPrayer, formatWait, quranCardMatch,
    zakatCalc, qiblaBearing, formatBearing, gregToHijri, hijriToGreg, formatHijri,
    upcomingFasts, daysLeftLabel, tasbihRoundInfo, TASBIH_PHRASES,
    mulberry32, quizQuestions, buildSearchIndex, searchIndex,
    planSchedule, planProgress, planDayOf, TOTAL_AYAH, shareText, clip,
} from '../../site/wp-content/themes/baaboul-houda/assets-src/js/lib.js';

describe('arNormalize', () => {
    it('strips diacritics and tatweel', () => {
        expect(arNormalize('بِسْمِ ٱللَّهِ')).toBe('بسم الله');
    });
    it('unifies alef variants', () => {
        expect(arNormalize('أَللَّٰهُ إلهه آملها إنا')).toContain('الله');
    });
    it('maps yeh to ya', () => {
        expect(arNormalize('قالْ')).toContain('قال');
    });
    it('handles empty input', () => {
        expect(arNormalize('')).toBe('');
        expect(arNormalize(null)).toBe('');
    });
});

describe('hhmmToMin', () => {
    it('parses valid times', () => {
        expect(hhmmToMin('05:30')).toBe(330);
        expect(hhmmToMin('23:59')).toBe(23 * 60 + 59);
        expect(hhmmToMin('00:00')).toBe(0);
    });
    it('rejects invalid times', () => {
        expect(hhmmToMin('24:00')).toBe(null);
        expect(hhmmToMin('12:60')).toBe(null);
        expect(hhmmToMin('')).toBe(null);
        expect(hhmmToMin('abc')).toBe(null);
    });
});

describe('nextPrayer', () => {
    const times = { fajr: '05:30', dhuhr: '12:15', asr: '15:45', maghrib: '18:20', isha: '19:35' };
    it('picks the soonest upcoming prayer', () => {
        const r = nextPrayer(times, new Date(2026, 9, 20, 13, 0)); // 13:00
        expect(r).toEqual({ key: 'asr', inMinutes: 165 });
    });
    it('returns null when all prayers are past', () => {
        const r = nextPrayer(times, new Date(2026, 9, 20, 20, 0));
        expect(r).toBe(null);
    });
    it('ignores malformed values', () => {
        const r = nextPrayer({ fajr: '05:30', dhuhr: '??' }, new Date(2026, 9, 20, 6, 0));
        expect(r).toBe(null);
    });
});

describe('formatWait', () => {
    it('formats minutes and hours', () => {
        expect(formatWait(45)).toBe('45 min');
        expect(formatWait(65)).toBe('1h 05m');
        expect(formatWait(0)).toBe('0 min');
        expect(formatWait(null)).toBe('');
    });
});

describe('quranCardMatch', () => {
    it('matches on normalized Arabic (ignoring diacritics)', () => {
        expect(quranCardMatch('الفاتحة', 'الفاتحة')).toBe(true);
        expect(quranCardMatch('ٱلْبَقَرَة', 'البقرة')).toBe(true);
    });
    it('matches on French names', () => {
        expect(quranCardMatch('al-fātiha الفاتحة', 'fatih')).toBe(true);
    });
    it('empty query matches everything', () => {
        expect(quranCardMatch('anything', '')).toBe(true);
        expect(quranCardMatch('anything', '   ')).toBe(true);
    });
});

describe('zakatCalc', () => {
    it('computes 2.5% of the net taxable wealth', () => {
        const r = zakatCalc({ cash: 100000, gold: 50000, business: 0, debts: 20000 }, 2.5, 0);
        expect(r.total).toBe(130000);
        expect(r.due).toBe(3250);
        expect(r.under).toBe(false);
    });
    it('flags wealth below the nisab and returns zero', () => {
        const r = zakatCalc({ cash: 50000 }, 2.5, 100000);
        expect(r.under).toBe(true);
        expect(r.due).toBe(0);
    });
    it('debts can never push the total below zero', () => {
        const r = zakatCalc({ cash: 100, debts: 500 }, 2.5, 0);
        expect(r.total).toBe(0);
        expect(r.due).toBe(0);
    });
});

describe('qiblaBearing', () => {
    it('is 0 from the Kaaba itself', () => {
        expect(qiblaBearing(21.422487, 39.826206)).toBeCloseTo(0, 1);
    });
    it('points east-northeast from Ouagadougou (~71°)', () => {
        expect(qiblaBearing(12.3714, -1.5197)).toBeCloseTo(71.4, 0);
    });
    it('points east-southeast from Paris (~119°)', () => {
        expect(qiblaBearing(48.8566, 2.3522)).toBeCloseTo(119.2, 0);
    });
});

describe('formatBearing', () => {
    it('maps degrees to compass points', () => {
        expect(formatBearing(0)).toBe('0.0° N');
        expect(formatBearing(45)).toBe('45.0° NE');
        expect(formatBearing(90)).toBe('90.0° E');
        expect(formatBearing(180)).toBe('180.0° S');
        expect(formatBearing(359)).toBe('359.0° N');
    });
    it('normalizes negative angles', () => {
        expect(formatBearing(-90)).toBe('270.0° W');
    });
});

describe('gregToHijri / hijriToGreg', () => {
    it('matches known Umm al-Qura anchors (2025-03-01 = 1 Ramaadan 1446)', () => {
        const h = gregToHijri(new Date('2025-03-01T12:00:00Z'));
        expect(h).toEqual({ y: 1446, m: 9, d: 1 });
    });
    it('formats French and Arabic month names', () => {
        const h = gregToHijri(new Date('2025-03-01T12:00:00Z'));
        expect(formatHijri(h, 'fr')).toBe('1 Ramaadan 1446');
        expect(formatHijri(h, 'ar')).toContain('رمضان');
    });
    it('round-trips through hijriToGreg', () => {
        for (const d of ['2026-01-01', '2027-05-15', '2030-01-01', '2040-07-07', '1999-12-31']) {
            const h = gregToHijri(new Date(`${d}T12:00:00Z`));
            const back = hijriToGreg(h.y, h.m, h.d);
            expect(back.toISOString().slice(0, 10)).toBe(d);
        }
    });
    it('rejects invalid input', () => {
        expect(Number.isNaN(hijriToGreg(0, 1, 1).getTime())).toBe(true);
        expect(Number.isNaN(hijriToGreg(1447, 13, 1).getTime())).toBe(true);
    });
});

describe('upcomingFasts', () => {
    it('returns a sorted, structured list from a fixed date', () => {
        const fasts = upcomingFasts(new Date('2026-10-20T12:00:00Z'), 400);
        expect(fasts.length).toBeGreaterThan(0);
        expect(fasts.every((f, i) => i === 0 || fasts[i - 1].date <= f.date)).toBe(true);
        const ids = new Set(fasts.map((f) => f.id));
        expect(ids).toContain('bid');
        expect(ids).toContain('ramadan');
        expect(ids).toContain('arafa');
        // 13-15 of every month within 400 days
        expect(fasts.filter((f) => f.id === 'bid').length).toBeGreaterThanOrEqual(30);
        // exactly one Ramadan + one Arafah + one Ashura inside the window
        expect(fasts.filter((f) => f.id === 'arafa').length).toBe(1);
        expect(fasts.filter((f) => f.id === 'asha').length).toBe(1);
    });
    it('labels days-left in both languages', () => {
        expect(daysLeftLabel(3, 'fr')).toBe('3 jours');
        expect(daysLeftLabel(1, 'fr')).toBe('1 jour');
        expect(daysLeftLabel(0, 'fr')).toBe("aujourd'hui");
        expect(daysLeftLabel(2, 'ar')).toContain('2');
    });
});

describe('tasbih', () => {
    it('lists the default phrases', () => {
        expect(TASBIH_PHRASES.length).toBe(5);
        expect(TASBIH_PHRASES[0].id).toBe('subhanallah');
    });
    it('computes rounds and remainder', () => {
        expect(tasbihRoundInfo(33, 33)).toEqual({ rounds: 1, left: 0, justDone: true });
        expect(tasbihRoundInfo(34, 33)).toEqual({ rounds: 1, left: 1, justDone: false });
        expect(tasbihRoundInfo(0, 33)).toEqual({ rounds: 0, left: 0, justDone: false });
    });
});

describe('mulberry32 / quizQuestions', () => {
    it('is deterministic for a given seed', () => {
        const a = mulberry32(7)();
        const b = mulberry32(7)();
        expect(a).toBe(b);
    });
    it('builds valid multiple-choice questions whose answer is the real next verse', () => {
        const texts = ['v1', 'v2', 'v3', 'v4', 'v5', 'v6', 'v7'];
        const qs = quizQuestions(texts, 5, 7);
        expect(qs.length).toBe(5);
        for (const q of qs) {
            expect(q.options).toHaveLength(4);
            expect(q.options[q.answer]).toBe(texts[q.pos]); // pos is 1-based → next verse is index pos
        }
        // deterministic
        expect(JSON.stringify(quizQuestions(texts, 5, 7))).toBe(JSON.stringify(qs));
    });
    it('returns an empty quiz for very short surahs', () => {
        expect(quizQuestions(['a', 'b'], 5, 1)).toEqual([]);
    });
});

describe('buildSearchIndex / searchIndex', () => {
    const surahs = [
        { n: 1, ayahs: ['بسم الله الرحمن الرحيم', 'إنهدى الله الناس جميعا'] },
        { n: 2, ayahs: ['بسم الله الغفور الرحيم'] },
    ];
    it('finds verses containing the query terms', () => {
        const idx = buildSearchIndex(surahs);
        const res = searchIndex(idx, 'بسم الله', (s, a) => `t${s}:${a}`, 10);
        expect(res.length).toBe(3);
        expect(res.map((r) => `${r.surah}:${r.ayah}`).sort()).toEqual(['1:1', '1:2', '2:1'].sort());
    });
    it('returns nothing for an empty query', () => {
        const idx = buildSearchIndex(surahs);
        expect(searchIndex(idx, '   ', () => 'x')).toEqual([]);
    });
    it('matches normalized terms (diacritics-insensitive)', () => {
        const idx = buildSearchIndex(surahs);
        expect(searchIndex(idx, 'الله', () => 'x').length).toBeGreaterThanOrEqual(3);
    });
});

describe('reading plan', () => {
    it('splits the 6236-verse Quran evenly', () => {
        expect(TOTAL_AYAH).toBe(6236);
        const p = planSchedule(30, new Date('2026-10-20'));
        expect(p.perDay).toBe(208);
        expect(planProgress(3118, p)).toBe(50);
        expect(planDayOf(0, p)).toBe(1);
        expect(planDayOf(416, p)).toBe(3);
    });
    it('clamps out-of-range durations (0 falls back to the 30-day default)', () => {
        expect(planSchedule(0, new Date()).days).toBe(30);
        expect(planSchedule(999, new Date()).days).toBe(90);
        expect(planSchedule(-5, new Date()).days).toBe(1);
    });
});

describe('share helpers', () => {
    it('builds title+url share text', () => {
        expect(shareText('Titre', 'https://x.bf/a')).toBe('Titre\nhttps://x.bf/a');
    });
    it('clips long strings with an ellipsis', () => {
        expect(clip('a'.repeat(100), 80).length).toBe(80);
        expect(clip('court')).toBe('court');
    });
});
