/**
 * TV Baaboul Houda — front-end (bundled with Vite; Alpine + hls.js + howler).
 * Components: radio bar, radio page, live HLS, prayer widget, Quran reader,
 * Quran index search, topbar date.
 */
import Alpine from 'alpinejs';
import Hls from 'hls.js';
import { Howl } from 'howler';
import { arNormalize, hhmmToMin, nextPrayer, formatWait, quranCardMatch } from './lib.js';

window.Alpine = Alpine;
window.Hls = Hls;
window.Howl = Howl;

const cfg = window.bhCfg || {};
const lang = (fr, ar) => (cfg.isAr ? ar : fr);

/* ============================ helpers ============================ */
const store = (key, val) => {
    try { return JSON.parse(localStorage.getItem(key)); } catch { return val; }
};
const save = (key, val) => { try { localStorage.setItem(key, JSON.stringify(val)); } catch { /* private mode */ } };



/* ============================ radio bar ============================ */
function bhRadioBar() {
    const el = document.querySelector('[data-bh-radio]');
    if (!el) return {};
    const url = el.dataset.url;
    let howl = null;
    return {
        playing: false,
        status: '',
        init() {
            const titleEl = el.querySelector('[data-radio-title]');
            const statusEl = el.querySelector('[data-radio-status]');
            const eqEl = el.querySelector('[data-bh-eq]');
            titleEl.textContent = cfg.isAr ? (el.dataset.titleAr || el.dataset.titleFr) : (el.dataset.titleFr || 'Radio');
            el.querySelector('[data-bh-radio-toggle]').addEventListener('click', () => {
                if (!howl) {
                    howl = new Howl({
                        src: [url],
                        format: url.endsWith('.m3u8') ? undefined : [url.match(/\.(mp3|m4a|aac|ogg)(\?|$)/) ? url.match(/\.(mp3|m4a|aac|ogg)/i)[0] : 'mp3'],
                        html5: true,
                        loop: true,
                        onplay() {
                            this.playing = true;
                            statusEl.textContent = lang('En direct', 'على الهواء');
                            eqEl.classList.add('is-playing');
                        },
                        onpause() { this.pause(); },
                        onend() { this.playing = false; },
                        onstop() { this.playing = false; eqEl.classList.remove('is-playing'); statusEl.textContent = ''; },
                        onloaderror() { statusEl.textContent = lang('Flux indisponible', 'الخدمة غير متاحة'); },
                        onplayerror() {
                            this.playing = false;
                            eqEl.classList.remove('is-playing');
                            statusEl.textContent = lang('Flux indisponible', 'الخدمة غير متاحة');
                        },
                    });
                }
                if (this.playing) {
                    howl.stop();
                    this.playing = false;
                    eqEl.classList.remove('is-playing');
                    statusEl.textContent = '';
                    el.querySelector('.icon-play').hidden = false;
                    el.querySelector('.icon-pause').hidden = true;
                } else {
                    howl.play();
                    this.playing = true;
                    el.querySelector('.icon-play').hidden = true;
                    el.querySelector('.icon-pause').hidden = false;
                }
            });
        },
    };
}

/* ============================ radio page ============================ */
function bhRadioPage(url, titleFr, titleAr, cover) {
    let howl = null;
    return {
        url, cover: cover || '',
        playing: false, vol: 0.9,
        init() {
            const statusEl = document.querySelector('[data-radio-status]');
            setInterval(() => {
                if (statusEl) statusEl.textContent = this.status();
            }, 2000);
        },
        title() { return cfg.isAr ? (titleAr || titleFr) : titleFr; },
        status() {
            if (!this.url) return lang('Bientôt disponible', 'قريبًا');
            if (!this.playing) return lang('Prêt à écouter', 'جاهز للاستماع');
            return lang('● En direct', '● على الهواء');
        },
        setVol() { if (howl) howl.volume(this.vol); },
        toggle() {
            if (!this.url) return;
            if (!howl) {
                howl = new Howl({
                    src: [this.url],
                    html5: true,
                    loop: true,
                    volume: this.vol,
                    onplay() { this.playing = true; },
                    onstop() { this.playing = false; },
                    onpause() { this.playing = false; },
                });
            }
            if (this.playing) howl.stop(); else howl.play();
        },
    };
}

/* ============================ prayer widget ============================ */
function bhPrayer(full = false) {
    const cities = cfg.prayer?.cities?.length ? cfg.prayer.cities : ['Ouagadougou'];
    const keys = ['fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha'];
    return {
        city: cities.includes(cfg.prayer?.city) ? cfg.prayer.city : cities[0],
        cities,
        times: cfg.prayer?.times || {},
        source: cfg.prayer?.source || 'fallback',
        nextKey: '',
        nextIn: '',
        hijriText: '',
        init() { this.load(); },
        get names() {
            return full ? {
                fajr: 'Fajr', sunrise: 'Lever', dhuhr: 'Dhuhr', asr: 'Asr', maghrib: 'Maghrib', isha: 'Isha',
            } : {
                fajr: 'Fajr', sunrise: 'Lever', dhuhr: 'Dhuhr', asr: 'Asr', maghrib: 'Maghrib', isha: 'Isha',
            };
        },
        get order() { return keys.map((k) => ({ key: k })); },
        hijri() { return this.hijriText; },
        async load() {
            const cacheKey = `bh-pt-${this.city}-${new Date().toISOString().slice(0, 10)}`;
            const cached = store(cacheKey, null);
            if (cached) { this.apply(cached.times, cached.hijri, 'aladhan'); return; }
            try {
                const d = new Date();
                const res = await fetch(`https://api.aladhan.com/v1/timingsByCity/${d.getDate()}-${d.getMonth() + 1}-${d.getFullYear()}?city=${encodeURIComponent(this.city)}&method=${cfg.prayer?.method || 3}`);
                if (!res.ok) throw new Error('http');
                const body = await res.json();
                const clean = (v) => String(v).replace(/\s*(AM|PM)$/, '').trim();
                const t = body.data.timings;
                const times = {
                    fajr: clean(t.Fajr), sunrise: clean(t.Sunrise), dhuhr: clean(t.Dhuhr),
                    asr: clean(t.Asr), maghrib: clean(t.Maghrib), isha: clean(t.Isha),
                };
                const hijri = `${body.data.date.hijri.day} ${cfg.isAr ? body.data.date.hijri.month.ar : body.data.date.hijri.month.en} ${body.data.date.hijri.year} هـ`;
                this.apply(times, hijri, 'aladhan');
                save(cacheKey, { times, hijri });
            } catch {
                this.apply(cfg.prayer?.times || {}, '', 'fallback');
            }
            this.tick();
        },
        apply(times, hijri, source) {
            this.times = times;
            this.hijriText = hijri || '';
            this.source = source;
            // topbar hijri
            document.querySelectorAll('[data-bh-hijri]').forEach((n) => { n.textContent = hijri || ''; });
            document.querySelectorAll('[data-bh-hijri-lg]').forEach((n) => { n.textContent = hijri || ''; });
        },
        tick() {
            const best = nextPrayer(this.times, new Date());
            this.nextKey = best ? best.key : '';
            this.nextIn = best ? formatWait(best.inMinutes) : '';
        },
    };
}

/* ============================ daily ayah ============================ */
function bhAyah() {
    const a = cfg.dailyAyah || {};
    return {
        surah: a.surah || 1,
        text: a.text || '',
        ref: a.name_ar ? `${a.name_ar} (${a.name_fr}) — ${a.surah}:${a.ayah}` : '',
        get coranUrl() { return cfg.coranUrl || '/coran/'; },
    };
}

/* ============================ Quran reader ============================ */
function bhReader(surahN) {
    const reciters = cfg.quran?.audioBases || {};
    let frCache = store(`bh-fr-${surahN}`, null);
    let reciter = store('bh-reciter', 'ar.alafasy');
    let allHls = null;
    let currentAudio = new Audio();
    let frRequest = null;
    return {
        surahN,
        reciter,
        showFr: store('bh-show-fr', true),
        allPlaying: false,
        init() {
            document.querySelectorAll('.bh-ayah').forEach((el) => {
                el.addEventListener('click', () => this.playAyah(Number(el.dataset.global), Number(el.dataset.ayah)));
            });
            window.addEventListener('beforeunload', () => this.stopAll());
        },
        frText(n) {
            if (!this.showFr) return '';
            if (frCache && frCache[n]) return frCache[n];
            this.ensureFr();
            return '';
        },
        ensureFr() {
            if (frRequest) return;
            frRequest = fetch(`https://api.alquran.cloud/v1/surah/${this.surahN}/editions/quran-simple,fr-cherrier`)
                .then((r) => { if (!r.ok) throw new Error('http'); return r.json(); })
                .then((body) => {
                    const fr = (body.data || []).find((e) => e.identifier === 'fr-cherrier') || (body.data || [])[1];
                    const out = {};
                    (fr?.ayahs || []).forEach((a) => { out[a.numberInSurah] = a.text; });
                    frCache = out;
                    save(`bh-fr-${this.surahN}`, out);
                    this.$nextTick(() => {
                        document.querySelectorAll('.bh-ayah__fr').forEach((el) => {
                            const ayah = Number(el.closest('.bh-ayah').dataset.ayah);
                            el.textContent = out[ayah] || '';
                        });
                    });
                })
                .catch(() => { /* FR translation unavailable offline */ })
                .finally(() => { frRequest = null; });
        },
        toggleAll() {
            if (this.allPlaying) { this.stopAll(); return; }
            const base = reciters[this.reciter];
            if (!base) return;
            this.allPlaying = true;
            const start = document.querySelector('.bh-ayah')?.dataset.global;
            const total = document.querySelectorAll('.bh-ayah').length;
            let i = 0;
            const playNext = () => {
                if (!this.allPlaying || i >= total || !start) { this.allPlaying = false; return; }
                const el = document.querySelectorAll('.bh-ayah')[i];
                currentAudio.src = base + Number(start) + i + '.mp3';
                currentAudio.play().catch(() => { this.allPlaying = false; });
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                currentAudio.onended = () => { i += 1; playNext(); };
            };
            playNext();
        },
        stopAll() {
            this.allPlaying = false;
            currentAudio.onended = null;
            currentAudio.pause();
            currentAudio.src = '';
        },
        playAyah(global, n) {
            const base = reciters[this.reciter];
            if (!base) return;
            currentAudio.onended = null;
            this.allPlaying = false;
            currentAudio.src = base + global + '.mp3';
            currentAudio.play().catch(() => {});
        },
    };
}

/* ============================ Quran index search ============================ */
function bhQuranIndex() {
    const index = cfg.quran?.index || [];
    const norm = arNormalize;
    return {
        q: '',
        count() { return `${index.length} sourates`; },
        matchCard(name) {
            return quranCardMatch(name, this.q);
        },
    };
}

/* ============================ live HLS ============================ */
function initLiveHls() {
    const video = document.getElementById('bh-live-video');
    if (!video) return;
    const src = video.dataset.src;
    if (!src) return;
    if (Hls.isSupported()) {
        const hls = new Hls();
        hls.loadSource(src);
        hls.attachMedia(video);
    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
        video.src = src;
    }
}

/* ============================ topbar date ============================ */
function initDate() {
    const el = document.querySelector('[data-bh-date]');
    if (!el) return;
    const d = new Date();
    el.textContent = cfg.isAr
        ? d.toLocaleDateString('ar-EG-u-nu-latn', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
        : d.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
}

/* ============================ boot ============================ */
Alpine.data('bhRadioPage', (u, f, a, c) => bhRadioPage(u, f, a, c));
Alpine.data('bhPrayer', (full) => bhPrayer(full));
Alpine.data('bhAyah', bhAyah);
Alpine.data('bhReader', (n) => bhReader(n));
Alpine.data('bhQuranIndex', bhQuranIndex);

document.addEventListener('alpine:init', () => {
    initLiveHls();
    initDate();
    if (cfg.accent) {
        document.documentElement.style.setProperty('--bh-green', cfg.accent);
    }
});

// The radio bar uses plain (non-Alpine) markup: wire it directly.
(function wireRadioBar() {
    const el = document.querySelector('[data-bh-radio]');
    if (!el) return;
    bhRadioBar().init();
})();

Alpine.start();
