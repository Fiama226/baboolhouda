import { describe, it, expect } from 'vitest';
import { arNormalize, hhmmToMin, nextPrayer, formatWait, quranCardMatch } from '../../site/wp-content/themes/baaboul-houda/assets-src/js/lib.js';

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
