/**
 * TV Baaboul Houda — front-end (bundled with Vite; Alpine).
 * Heavy libs (hls.js, howler, qrcode) are lazy-loaded on demand.
 * Components: radio bar/page, live (channels, countdown, QR, quality),
 * prayer widget, Quran index (resume + full-text search), tools
 * (zakat, qibla, dates, tasbih), forms (newsletter, wall, question),
 * dark mode, live toast, WhatsApp float, adhan, share cards, podcast players.
 */
import Alpine from 'alpinejs';
import {
    arNormalize, hhmmToMin, nextPrayer, formatWait, quranCardMatch,
    zakatCalc, qiblaBearing, formatBearing, gregToHijri, hijriToGreg, formatHijri,
    upcomingFasts, daysLeftLabel, tasbihPhrase, tasbihRoundInfo,
    buildSearchIndex, searchIndex, shareText, clip,
} from './lib.js';

window.Alpine = Alpine;

const cfg = window.bhCfg || {};
const lang = (fr, ar) => (cfg.isAr ? ar : fr);

const store = (key, val) => {
    try { const v = JSON.parse(localStorage.getItem(key)); return v === null || v === undefined ? val : v; } catch { return val; }
};
const save = (key, val) => { try { localStorage.setItem(key, JSON.stringify(val)); } catch { /* private mode */ } };

let HowlCtor = null;
const howler = () => HowlCtor || (HowlCtor = import('howler').then((m) => m.Howl));
let HlsCtor = null;
const hlsLib = () => HlsCtor || (HlsCtor = import('hls.js').then((m) => m.default || m));

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
            el.querySelector('[data-bh-radio-toggle]').addEventListener('click', async () => {
                if (!howl) {
                    const Howl = await howler();
                    howl = new Howl({
                        src: [url],
                        html5: true,
                        loop: true,
                        onplay() {
                            this.playing = true;
                            statusEl.textContent = lang('En direct', 'على الهواء');
                            eqEl.classList.add('is-playing');
                        },
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
        async toggle() {
            if (!this.url) return;
            if (!howl) {
                const Howl = await howler();
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

/* ============================ live player (hero) ============================ */
async function drawQr(canvas, text) {
    try {
        const QRCode = await import('qrcode');
        await QRCode.toCanvas(canvas, text, { width: 220, margin: 1, color: { dark: '#08130d', light: '#ffffff' } });
    } catch {
        // QR unavailable (offline): keep the URL text below the (blank) canvas.
    }
}

function bhLive(o) {
    const ch1 = o.ch1 || {};
    const ch2 = o.ch2 || {};
    const now = o.now || {};
    return {
        ch: 1,
        quality: 'auto',
        showQr: false,
        hasCh2: Boolean(ch2.enabled),
        ch1, ch2,
        cd: '',
        justTitle: '',
        justUrl: '',
        init() {
            this.tick();
            this.timer = setInterval(() => this.tick(), 20000);
            this.$watch('showQr', (v) => { if (v) drawQr(document.getElementById('bh-live-qr'), o.page); });
            window.addEventListener('beforeunload', () => clearInterval(this.timer));
        },
        currentOn() {
            if (this.ch === 2) return Boolean(ch2.enabled);
            return Boolean(ch1.enabled);
        },
        currentPlatform() {
            return this.ch === 2 ? ch2.platform : ch1.platform;
        },
        currentTitle() {
            // Titles are pre-localized by PHP (bh_l) before being passed in.
            return this.ch === 2 ? (ch2.title || ch1.title) : ch1.title;
        },
        liveLabel() { return lang('● EN DIRECT', '● بث مباشر'); },
        offLabel() { return lang('Hors antenne', 'خارج البث'); },
        nextTitle() { return now.next ? now.next.title : ''; },
        nextStart() { return now.next ? now.next.start : ''; },
        countdown() { return this.cd; },
        justEndedTitle() { return this.justTitle; },
        justEndedUrl() { return this.justUrl; },
        qrText() { return o.page || location.href; },
        tick() {
            const d = new Date();
            const nowMin = d.getHours() * 60 + d.getMinutes();
            const slots = now.slots || [];
            let best = null;
            for (const s of slots) {
                const m = hhmmToMin(s.start);
                if (m === null) continue;
                let diff = m - nowMin;
                if (diff < 0) diff += 24 * 60; // tomorrow
                if (diff > 0 && diff < 24 * 60 && (best === null || diff < best)) {
                    best = diff;
                }
            }
            this.cd = best === null ? '' : lang(`dans ${formatWait(best)}`, `بعد ${formatWait(best)}`);
            // just ended: a slot that finished 0–120 minutes ago
            this.justTitle = '';
            this.justUrl = '';
            for (const s of slots) {
                const e = hhmmToMin(s.end);
                if (e === null) continue;
                let ago = nowMin - e;
                if (ago < 0) ago += 24 * 60;
                if (ago >= 0 && ago <= 120 && s.programme_id) {
                    this.justTitle = s.title || '';
                    this.justUrl = ''; // programme pages are pretty URLs; deep link not always known
                    break;
                }
            }
        },
        async applyQuality() {
            const video = document.getElementById('bh-live-video');
            if (!video || !window.__bhLiveHls) return;
            try {
                await window.__bhLiveHls.setQuality(this.quality);
            } catch { /* quality change unavailable for this stream */ }
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
        init() { this.load(); setInterval(() => this.tick(), 30000); },
        get names() {
            return cfg.isAr
                ? { fajr: 'الفجر', sunrise: 'الشروق', dhuhr: 'الظهر', asr: 'العصر', maghrib: 'المغرب', isha: 'العشاء' }
                : { fajr: 'Fajr', sunrise: 'Lever', dhuhr: 'Dhuhr', asr: 'Asr', maghrib: 'Maghrib', isha: 'Isha' };
        },
        get order() { return keys.map((k) => ({ key: k })); },
        hijri() { return this.hijriText; },
        async load() {
            const cacheKey = `bh-pt-${this.city}-${new Date().toISOString().slice(0, 10)}`;
            const cached = store(cacheKey, null);
            if (cached) { this.apply(cached.times, cached.hijri, 'aladhan'); this.tick(); return; }
            try {
                const d = new Date();
                const dd = `${String(d.getDate()).padStart(2, '0')}-${String(d.getMonth() + 1).padStart(2, '0')}-${d.getFullYear()}`;
                const c = cfg.prayer?.coords?.[this.city];
                const url = c
                    ? `https://api.aladhan.com/v1/timings/${dd}?latitude=${c.lat}&longitude=${c.lon}&method=${cfg.prayer?.method || 3}`
                    : `https://api.aladhan.com/v1/timingsByCity/${dd}?city=${encodeURIComponent(this.city)}&country=Burkina%20Faso&method=${cfg.prayer?.method || 3}`;
                const res = await fetch(url);
                if (!res.ok) throw new Error('http');
                const body = await res.json();
                const clean = (v) => String(v).replace(/\s*(\(.*\)|AM|PM)\s*$/i, '').trim();
                const t = body.data.timings;
                const times = {
                    fajr: clean(t.Fajr), sunrise: clean(t.Sunrise), dhuhr: clean(t.Dhuhr),
                    asr: clean(t.Asr), maghrib: clean(t.Maghrib), isha: clean(t.Isha),
                };
                const hijri = `${body.data.date.hijri.day} ${cfg.isAr ? body.data.date.hijri.month.ar : body.data.date.hijri.month.en} ${body.data.date.hijri.year}`;
                this.apply(times, hijri, 'aladhan');
                save(cacheKey, { times, hijri });
            } catch {
                this.apply(cfg.prayer?.times || {}, '', 'fallback');
            }
            this.tick();
        },
        apply(times, hijri, source) {
            this.times = times;
            this.hijriText = hijri || formatHijri(gregToHijri(new Date()), cfg.isAr ? 'ar' : 'fr');
            this.source = source;
            document.querySelectorAll('[data-bh-hijri]').forEach((n) => { n.textContent = this.hijriText; });
            document.querySelectorAll('[data-bh-hijri-lg]').forEach((n) => { n.textContent = this.hijriText; });
            window.__bhHijriText = this.hijriText;
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

/* ============================ zakat ============================ */
function bhZakat(o) {
    return {
        cash: 0, gold: 0, business: 0, debts: 0,
        rate: o.rate || 2.5,
        nisab: o.nisab || 0,
        nisabNote: o.nisabNote || '',
        total() {
            const r = zakatCalc({ cash: this.cash, gold: this.gold, business: this.business, debts: this.debts }, this.rate, this.nisab);
            return r.total;
        },
        zakatDue() {
            const r = zakatCalc({ cash: this.cash, gold: this.gold, business: this.business, debts: this.debts }, this.rate, this.nisab);
            return `${r.due.toLocaleString(cfg.isAr ? 'ar' : 'fr-FR')} ${o.currency || ''}`;
        },
        nisabText() {
            return this.nisab > 0
                ? `${this.nisab.toLocaleString(cfg.isAr ? 'ar' : 'fr-FR')} ${o.currency || ''} (${this.nisabNote})`
                : this.nisabNote || (cfg.isAr ? 'قيمة 85 غ من الذهب' : 'valeur de 85 g d’or');
        },
    };
}

/* ============================ qibla ============================ */
function bhQibla(o) {
    const cities = o.cities && o.cities.length ? o.cities : ['Ouagadougou'];
    let heading = 0;
    let handler = null;
    return {
        citySel: cities.includes(o.city) ? o.city : cities[0],
        cities,
        bearingDeg: typeof o.bearing === 'number' ? o.bearing : null,
        deviceReady: false,
        needleBase: 0,
        init() { this.applyCity(); },
        bearingText() {
            if (this.bearingDeg === null) return '';
            return formatBearing(this.bearingDeg);
        },
        needleDeg() {
            if (this.deviceReady) return -heading; // Kaaba marker fixed at top; rotate dial by -heading
            return this.bearingDeg === null ? 0 : this.bearingDeg;
        },
        applyCity() {
            // PHP already pre-computed for the default city; for other cities
            // compute client-side with known coords when possible.
            if (window.__bhQiblaByCity && window.__bhQiblaByCity[this.citySel] !== undefined) {
                this.bearingDeg = window.__bhQiblaByCity[this.citySel];
            } else if (typeof o.bearing === 'number' && this.citySel === (o.city || this.citySel)) {
                this.bearingDeg = o.bearing;
            }
        },
        startCompass() {
            const onOri = (e) => {
                const a = e.webkitCompassHeading !== undefined ? e.webkitCompassHeading : e.alpha;
                if (a === null || a === undefined) return;
                heading = (360 - Number(a)) % 360;
                this.$nextTick(() => {});
            };
            if (typeof DeviceOrientationEvent !== 'undefined' && typeof DeviceOrientationEvent.requestPermission === 'function') {
                DeviceOrientationEvent.requestPermission().then((p) => {
                    if (p === 'granted') { window.addEventListener('deviceorientationabsolute', onOri, true); window.addEventListener('deviceorientation', onOri, true); this.deviceReady = true; }
                }).catch(() => {});
            } else {
                window.addEventListener('deviceorientationabsolute', onOri, true);
                window.addEventListener('deviceorientation', onOri, true);
                this.deviceReady = true;
                handler = onOri;
            }
            window.__bhQiblaHandler = handler;
        },
    };
}

/* ============================ dates & calendar ============================ */
function bhDates(o) {
    const now = new Date();
    const h = gregToHijri(now);
    return {
        todayGreg() { return now.toLocaleDateString(cfg.isAr ? 'ar-EG-u-nu-latn' : 'fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }); },
        todayHijri() { return formatHijri(h, cfg.isAr ? 'ar' : 'fr'); },
        resultText() { return this.result; },
        fasts: upcomingFasts(now, 400),
        gregDate: now.toISOString().slice(0, 10),
        hijriYear: h.y,
        hijriMonth: h.m,
        hijriDay: h.d,
        hijriMonthName: '',
        result: '',
        fullText: false,
        searchResults: [],
        init() {},
        nextEvent() {
            const upcoming = this.fasts.filter((f) => f.daysLeft > 0);
            const e = upcoming[0] || this.fasts[0] || { name: '', date: '', daysLeft: 0 };
            return {
                name: cfg.isAr ? e.name_ar : e.name_fr,
                date: e.date instanceof Date
                    ? e.date.toLocaleDateString(cfg.isAr ? 'ar-EG-u-nu-latn' : 'fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })
                    : String(e.date || ''),
                daysLeft: e.daysLeft,
                days: e.daysLeft,
            };
        },
        upcomingFasts() {
            return this.fasts.slice(0, 14).map((f) => ({
                id: `${f.id}-${f.date.toISOString().slice(0, 10)}`,
                name: cfg.isAr ? f.name_ar : f.name_fr,
                date: f.date.toLocaleDateString(cfg.isAr ? 'ar-EG-u-nu-latn' : 'fr-FR', { weekday: 'short', day: 'numeric', month: 'short' }),
                daysLeft: daysLeftLabel(f.daysLeft, cfg.isAr ? 'ar' : 'fr'),
            }));
        },
        convertGregToHijri() {
            if (!this.gregDate) return;
            const d = new Date(`${this.gregDate}T12:00:00`);
            if (Number.isNaN(d.getTime())) return;
            const hh = gregToHijri(d);
            this.result = cfg.isAr
                ? `${d.toLocaleDateString('ar-EG-u-nu-latn')} = ${formatHijri(hh, 'ar')}`
                : `${d.toLocaleDateString('fr-FR')} = ${formatHijri(hh, 'fr')}`;
        },
        convertHijriToGreg() {
            const d = hijriToGreg(this.hijriYear, this.hijriMonth, this.hijriDay);
            if (Number.isNaN(d.getTime())) return;
            this.result = cfg.isAr
                ? `${formatHijri({ y: this.hijriYear, m: this.hijriMonth, d: this.hijriDay }, 'ar')} = ${d.toLocaleDateString('ar-EG-u-nu-latn', { day: 'numeric', month: 'long', year: 'numeric' })}`
                : `${formatHijri({ y: this.hijriYear, m: this.hijriMonth, d: this.hijriDay }, 'fr')} = ${d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })}`;
        },
    };
}

/* ============================ ramadan ============================ */
function bhRamadan() {
    const maghribToday = () => {
        const c = store(`bh-pt-${cfg.prayer?.city || 'Ouagadougou'}-${new Date().toISOString().slice(0, 10)}`, null);
        return (c && c.times && c.times.maghrib) || cfg.prayer?.times?.maghrib || '18:30';
    };
    return {
        left: '',
        init() {
            this.refresh();
            setInterval(() => this.refresh(), 1000);
        },
        inRamadan() { return gregToHijri(new Date()).m === 9; },
        refresh() {
            const m = /^(\d{1,2}):(\d{2})/.exec(maghribToday());
            if (!m) { this.left = ''; return; }
            const t = new Date();
            t.setHours(Number(m[1]), Number(m[2]), 0, 0);
            let diff = Math.floor((t.getTime() - Date.now()) / 1000);
            if (diff < 0) diff = 0;
            const p = (n) => String(n).padStart(2, '0');
            this.left = `${p(Math.floor(diff / 3600))}:${p(Math.floor((diff % 3600) / 60))}:${p(diff % 60)}`;
        },
        iftarCountdown() { return this.left; },
    };
}
Alpine.data('bhRamadan', bhRamadan);

/* ============================ tasbih ============================ */
function bhTasbih() {
    const key = 'bh-tasbih';
    const st = store(key, { phrase: 'subhanallah', goal: 33, counts: {} });
    const counts = st.counts || {};
    return {
        phrase: st.phrase || 'subhanallah',
        goal: st.goal || 33,
        init() { this.persist(); },
        count: counts[this.phrase] || 0,
        phraseAr() { return tasbihPhrase(this.phrase).ar; },
        roundsText() {
            const r = tasbihRoundInfo(this.count, this.goal);
            return cfg.isAr
                ? `${r.rounds} دورة · ${r.left} / ${this.goal}`
                : `${r.rounds} tour(s) · ${r.left} / ${this.goal}`;
        },
        tick() {
            this.count += 1;
            const r = tasbihRoundInfo(this.count, this.goal);
            if (r.justDone) {
                try { navigator.vibrate && navigator.vibrate(60); } catch { /* no haptics */ }
            }
            this.persist();
        },
        reset() {
            counts[this.phrase] = 0;
            this.count = 0;
            this.persist();
        },
        onPhraseChange() {
            this.count = counts[this.phrase] || 0;
            this.persist();
        },
        persist() {
            counts[this.phrase] = this.count;
            save(key, { phrase: this.phrase, goal: this.goal, counts });
        },
    };
}

/* ============================ newsletter ============================ */
function bhNewsletterForm(o) {
    return {
        email: '',
        busy: false,
        msg: '',
        ok: false,
        async subscribe() {
            const hp = this.$refs.hp;
            if (hp && hp.value) return; // honeypot
            if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(this.email)) {
                this.ok = false; this.msg = lang('Email invalide.', 'بريد غير صالح.');
                return;
            }
            this.busy = true; this.msg = '';
            try {
                const res = await fetch(o.rest, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: this.email }),
                });
                const body = await res.json().catch(() => ({}));
                this.ok = Boolean(res.ok);
                this.msg = body.message || (this.ok ? lang('Inscription confirmée !', 'تم التسجيل!') : lang('Erreur — réessayez.', 'خطأ — أعد المحاولة.'));
            } catch {
                this.ok = false;
                this.msg = lang('Impossible de se connecter — réessayez.', 'تعذر الاتصال — أعد المحاولة.');
            }
            this.busy = false;
        },
    };
}

/* ============================ views counter ============================ */
function bhViews(n) {
    return { views: n || 0 };
}

/* ============================ Quran index (resume + search) ============================ */
function bhQuranIndex() {
    const index = cfg.quran?.index || [];
    let searchIdx = null;
    return {
        q: '',
        view: 'surahs',
        fullText: false,
        hits: [],
        resume: store('bh-last-read', null),
        initAll() {
            let t = null;
            const run = () => { clearTimeout(t); t = setTimeout(() => this.runSearch(), 300); };
            this.$watch('q', run);
            this.$watch('fullText', run);
        },
        async runSearch() {
            if (!this.fullText) { this.hits = []; return; }
            this.hits = await this.searchVerses();
        },
        count() { return `${index.length} ${lang('sourates', 'سورة')}`; },
        matchCard(name) { return quranCardMatch(name, this.q); },
        resumeUrl() {
            if (!this.resume || !this.resume.surah) return '';
            return `${cfg.coranUrl || '/coran/'}${this.resume.surah}/#a-${this.resume.ayah || 1}`;
        },
        resumeLabel() {
            if (!this.resume || !this.resume.surah) return '';
            const su = index.find((s) => s.n === this.resume.surah);
            return su ? `${su.fr} ${su.n} · ${lang('verset', 'آية')} ${this.resume.ayah}` : '';
        },
        async searchVerses() {
            try {
                const norm = arNormalize(this.q.trim().toLowerCase());
                if (norm.length < 2) { this._cache = []; return []; }
                // 1) API search (FR + AR) when online.
                const params = new URLSearchParams({ q: this.q.trim(), size: '12', language: cfg.isAr ? 'ar' : 'fr' });
                const res = await fetch(`https://api.quran.com/api/v4/search?${params}`);
                if (res.ok) {
                    const body = await res.json();
                    const list = (body.search?.results || []).map((v) => {
                        const [sn, an] = String(v.verse_key || '1:1').split(':');
                        return {
                            ref: `${sn}:${an}`,
                            url: `${cfg.coranUrl || '/coran/'}${sn}/#a-${an}`,
                            text: clip(String(v.text || ''), 120),
                        };
                    });
                    this._cache = list;
                    return list;
                }
                throw new Error('api down');
            } catch {
                // 2) Offline: search the locally cached surahs (localStorage).
                this._cache = this.localSearch();
                return this._cache;
            }
        },
        localSearch() {
            if (!searchIdx) {
                const surahs = [];
                for (let n = 1; n <= 114; n++) {
                    const data = store(`bh-ar-${n}`, null);
                    if (data && data.length) surahs.push({ n, ayahs: data });
                }
                searchIdx = buildSearchIndex(surahs);
            }
            if (!searchIdx.size) return [];
            return searchIndex(searchIdx, this.q, (s, a) => store(`bh-ar-${s}`, null)?.[a - 1] || '', 12)
                .map((r) => ({
                    ref: `${r.surah}:${r.ayah}`,
                    url: `${cfg.coranUrl || '/coran/'}${r.surah}/#a-${r.ayah}`,
                    text: clip(r.snippet, 120),
                }));
        },
        resultsHint() {
            return lang(
                'Recherche en ligne — ou consultez votre cache local hors connexion.',
                'بحث عبر الإنترنت — أو استعرض ذاكرتك المحلية دون اتصال.'
            );
        },
    };
}

/* ============================ live HLS (lazy) ============================ */
async function initLiveHls() {
    const video = document.getElementById('bh-live-video');
    if (!video) return;
    const src = video.dataset.src;
    if (!src) return;
    const Lib = await hlsLib();
    let hls = null;
    if (Lib.isSupported()) {
        hls = new Lib();
        hls.loadSource(src);
        hls.attachMedia(video);
        window.__bhLiveHls = {
            async setQuality(q) {
                if (!hls) return;
                if (q === 'auto') { hls.currentLevel = -1; return; }
                const maxH = Number(q);
                const levels = hls.levels || [];
                const eligible = levels.filter((l) => l.height && l.height <= maxH);
                if (!eligible.length) { hls.currentLevel = -1; return; }
                const target = Math.max(...eligible.map((l) => l.height));
                const idx = levels.findIndex((l) => l.height === target);
                hls.currentLevel = idx >= 0 ? idx : -1;
            },
        };
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
    // Hijri date on every page (the prayer widget replaces it with the Aladhan value where it runs).
    const hijri = formatHijri(gregToHijri(d), cfg.isAr ? 'ar' : 'fr');
    document.querySelectorAll('[data-bh-hijri]').forEach((n) => { if (!n.textContent) n.textContent = hijri; });
}

/* ============================ dark mode ============================ */
function initTheme() {
    // One toggle in the bar, one in the mobile sheet; moon/sun are both in
    // the markup and CSS shows the right one per .bh-dark.
    const btns = document.querySelectorAll('[data-bh-theme-toggle]');
    const apply = (dark) => {
        document.documentElement.classList.toggle('bh-dark', dark);
        btns.forEach((b) => b.setAttribute('aria-pressed', dark ? 'true' : 'false'));
    };
    const saved = store('bh-theme', null);
    apply(saved ? saved === 'dark' : window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
    btns.forEach((b) => b.addEventListener('click', () => {
        const dark = !document.documentElement.classList.contains('bh-dark');
        apply(dark);
        save('bh-theme', dark ? 'dark' : 'light');
    }));
}

/* ============================ WhatsApp float ============================ */
function initWaFloat() {
    const el = document.querySelector('[data-bh-wa-float]');
    if (!el) return;
    const num = String(cfg.whatsapp || '').replace(/[^0-9]/g, '');
    if (!num) return;
    el.href = `https://wa.me/${num}`;
    el.style.display = 'flex';
}

/* ============================ live toast + notifications ============================ */
function initLiveToast() {
    const el = document.getElementById('bh-live-toast');
    if (!el || !cfg.rest || !cfg.rest.status) return;
    let prev = null;
    const check = async () => {
        try {
            const res = await fetch(cfg.rest.status, { credentials: 'omit' });
            if (!res.ok) return;
            const body = await res.json();
            const on = Boolean(body.live && body.live.enabled);
            if (prev === null) { prev = on; return; }
            if (on && !prev) {
                showToast(cfg.isAr ? '● La chaîne est en direct !' : '● La chaîne est en direct !');
                if (typeof Notification !== 'undefined' && Notification.permission === 'granted') {
                    try { new Notification('TV Baaboul Houda', { body: cfg.isAr ? 'بث مباشر الآن' : 'En direct maintenant' }); } catch { /* */ }
                }
            }
            prev = on;
        } catch { /* offline */ }
    };
    const showToast = (text) => {
        el.hidden = false;
        el.textContent = text;
        el.classList.add('is-show');
        setTimeout(() => { el.classList.remove('is-show'); setTimeout(() => { el.hidden = true; }, 400); }, 30000);
    };
    check();
    setInterval(check, 90000);
    if (typeof Notification !== 'undefined' && Notification.permission === 'default') {
        // Ask lazily — only after a user gesture.
        window.addEventListener('click', function ask() {
            Notification.requestPermission().then((p) => { if (p === 'granted') showToast(lang('Notifications activées pour le direct.', 'تم تفعيل إشعارات البث المباشر.')); });
            window.removeEventListener('click', ask);
        }, { once: true });
    }
}

/* ============================ adhan on prayer time ============================ */
function initAdhan() {
    const audio = document.querySelector('[data-bh-adhan]');
    if (!audio) return;
    let lastSlot = '';
    const play = () => {
        const t = store(`bh-pt-${cfg.prayer?.city || 'Ouagadougou'}-${new Date().toISOString().slice(0, 10)}`, null);
        const times = (t && t.times) || cfg.prayer?.times || {};
        const now = new Date();
        const key = `${now.toISOString().slice(0, 10)}`;
        for (const k of ['fajr', 'dhuhr', 'asr', 'maghrib', 'isha']) {
            if (times[k] && times[k].slice(0, 5) === now.toTimeString().slice(0, 5)) {
                const slotId = `${key}-${k}`;
                if (lastSlot === slotId) return;
                lastSlot = slotId;
                try { audio.currentTime = 0; audio.play().catch(() => {}); } catch { /* */ }
                // PWA: also fire a notification (when granted).
                if ('Notification' in window && Notification.permission === 'granted') {
                    const label = { fajr: 'Fajr', dhuhr: 'Dhuhr', asr: 'Asr', maghrib: 'Maghrib', isha: 'Isha' }[k] || k;
                    try {
                        const note = new Notification(label, {
                            body: cfg.isAr ? `وقت ${label} — ${times[k]}` : `Prière de ${label} — ${times[k]}`,
                            icon: `${location.origin}/site/wp-content/themes/baaboul-houda/assets/icons/icon-192.png`,
                            tag: slotId,
                        });
                        note.onclick = () => { window.focus(); note.close(); };
                    } catch { /* notifications blocked in some contexts */ }
                }
            }
        }
    };
    setInterval(play, 20000);
}

/* ============================ share cards (canvas) ============================ */
function drawShareCard(canvas, title, url) {
    const W = 1200, H = 630;
    const ctx = canvas.getContext('2d');
    const accent = cfg.accent || '#0e7a4e';
    ctx.fillStyle = '#08130d';
    ctx.fillRect(0, 0, W, H);
    ctx.fillStyle = accent;
    ctx.fillRect(0, 0, W, 14);
    ctx.fillStyle = '#ffffff';
    ctx.font = '600 44px Cairo, sans-serif';
    ctx.fillText(cfg.isAr ? 'قناة باب الهدى' : 'TV Baaboul Houda', 60, 110);
    ctx.font = '700 56px Cairo, sans-serif';
    const words = String(title || '').split(' ');
    let line = '';
    let y = 260;
    for (const w of words) {
        const test = line ? `${line} ${w}` : w;
        if (ctx.measureText(test).width > W - 120) {
            ctx.fillText(line, 60, y);
            line = w;
            y += 74;
            if (y > 500) break;
        } else line = test;
    }
    if (y <= 500) ctx.fillText(line, 60, y);
    ctx.font = '400 30px Cairo, sans-serif';
    ctx.fillStyle = '#9fe3c3';
    ctx.fillText(String(url || ''), 60, H - 60);
}

function initShare() {
    document.querySelectorAll('[data-bh-share]').forEach((el) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'bh-share__btn';
        const title = el.dataset.title || document.title;
        const url = el.dataset.url || location.href;
        btn.textContent = lang('Carte de partage (image)', 'بطاقة مشاركة (صورة)');
        btn.addEventListener('click', () => {
            const canvas = document.createElement('canvas');
            canvas.width = 1200; canvas.height = 630;
            drawShareCard(canvas, title, url);
            canvas.toBlob((blob) => {
                if (!blob) return;
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = `baaboul-houda-${Date.now()}.png`;
                a.click();
                setTimeout(() => URL.revokeObjectURL(a.href), 4000);
            }, 'image/png');
        });
        el.appendChild(btn);
    });
}

/* ============================ podcast audio players ============================ */
function initPodcastPlayers() {
    document.querySelectorAll('[data-bh-audio-player]').forEach((card) => {
        const btn = card.querySelector('.bh-audio-play');
        if (!btn) return;
        let audio = null;
        let playing = false;
        btn.addEventListener('click', () => {
            if (!audio) {
                audio = new Audio(card.dataset.url);
                audio.addEventListener('ended', () => { playing = false; btn.textContent = '▶'; card.classList.remove('is-playing'); });
            }
            if (playing) {
                audio.pause();
                playing = false;
                btn.textContent = '▶';
                card.classList.remove('is-playing');
            } else {
                audio.play().then(() => {
                    playing = true;
                    btn.textContent = '⏸';
                    card.classList.add('is-playing');
                }).catch(() => {});
            }
        });
    });
}

/* ============================ boot ============================ */
/** Header live search (suggest from REST /bh/v1/search). */
function bhSearch() {
    let timer = null;
    return {
        q: '',
        open: false,
        panel: false,
        busy: false,
        results: [],
        toggle() {
            this.panel = !this.panel;
            if (this.panel) this.$nextTick(() => this.$refs.q && this.$refs.q.focus());
            else this.open = false;
        },
        close() {
            this.panel = false;
            this.open = false;
        },
        _timer() {
            clearTimeout(timer);
            timer = setTimeout(() => this.run(), 250);
        },
        async run() {
            const q = this.q.trim();
            if (q.length < 2) {
                this.results = [];
                this.open = false;
                return;
            }
            this.busy = true;
            try {
                const res = await fetch(`${cfg.rest?.search || '/wp-json/bh/v1/search'}${(cfg.rest?.search || '').includes('?') ? '&' : '?'}q=${encodeURIComponent(q)}`);
                if (res.ok) {
                    const body = await res.json();
                    this.results = Array.isArray(body.results) ? body.results.slice(0, 8) : [];
                }
            } catch {
                this.results = [];
            }
            this.busy = false;
            this.open = this.results.length > 0;
        },
        submit() {
            const q = this.q.trim();
            if (q) window.location.href = (cfg.home || '/') + `?s=${encodeURIComponent(q)}`;
        },
    };
}
Alpine.data('bhSearch', bhSearch);
Alpine.data('bhRadioPage', (u, f, a, c) => bhRadioPage(u, f, a, c));
Alpine.data('bhPrayer', (full) => bhPrayer(full));
Alpine.data('bhAyah', bhAyah);
Alpine.data('bhQuranIndex', bhQuranIndex);
Alpine.data('bhLive', (o) => bhLive(o));
Alpine.data('bhZakat', (o) => bhZakat(o));
Alpine.data('bhQibla', (o) => bhQibla(o));
Alpine.data('bhDates', (o) => bhDates(o));
Alpine.data('bhTasbih', bhTasbih);
Alpine.data('bhNewsletter', (o) => bhNewsletterForm(o));
Alpine.data('bhNewsletterFooter', (o) => bhNewsletterForm(o));
Alpine.data('bhViews', (n) => bhViews(n));

document.addEventListener('alpine:init', () => {
    initDate();
    initTheme();
    initWaFloat();
    initLiveToast();
    initAdhan();
    initShare();
    initPodcastPlayers();
    if (cfg.accent) {
        document.documentElement.style.setProperty('--bh-green', cfg.accent);
    }
});

// qibla bearings precomputed per city (server data)
if (cfg.qiblaBears && Object.keys(cfg.qiblaBears).length) {
    window.__bhQiblaByCity = cfg.qiblaBears;
}

(async function bootHls() {
    await initLiveHls();
})();

// The radio bar uses plain (non-Alpine) markup: wire it directly.
(function wireRadioBar() {
    const el = document.querySelector('[data-bh-radio]');
    if (!el) return;
    bhRadioBar().init();
})();

// Reader pages: register the lazy quran.js components (bhReader, bhPlan)
// BEFORE Alpine walks the DOM, otherwise x-data="bhReader(…)" is undefined.
if (document.querySelector('[data-bh-reader]')) {
    import('./quran.js').then(() => Alpine.start(), () => Alpine.start());
} else {
    Alpine.start();
}
