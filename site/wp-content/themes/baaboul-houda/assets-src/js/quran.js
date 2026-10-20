/**
 * Quran reader — lazy-loaded on /coran/ pages.
 * Features: reciters, FR editions, speed/repeat, sequential play + highlight,
 * simplified script, tafsir, word-by-word, share cards, bookmarks, notes,
 * reading plan, memorization quiz. Heavy data (translations, tafsir) is
 * fetched at runtime and cached in localStorage.
 */
import Alpine from 'alpinejs';
import { mulberry32, quizQuestions, planSchedule, planProgress, planDayOf, clip, shareText } from './lib.js';

const cfg = window.bhCfg || {};
const lang = (fr, ar) => (cfg.isAr ? ar : fr);

/* Draw the 1200x630 verse card and resolve to a PNG Blob (or null). Never rejects. */
async function buildAyahCard(arText, title, url, accent) {
    try {
        const W = 1200, H = 630;
        const quranFont = '"Amiri Quran", Amiri, "Traditional Arabic", serif';
        try {
            if (document.fonts) {
                await Promise.all([
                    document.fonts.load(`400 64px "Amiri Quran"`, arText || 'ا'),
                    document.fonts.load('600 34px Cairo', title),
                ]);
                await document.fonts.ready;
            }
        } catch { /* fonts are best-effort */ }
        const canvas = document.createElement('canvas');
        canvas.width = W; canvas.height = H;
        const ctx = canvas.getContext('2d');
        if (!ctx) return null;
        const grad = ctx.createLinearGradient(0, 0, W, H);
        grad.addColorStop(0, '#08130d'); grad.addColorStop(1, '#0d2a1b');
        ctx.fillStyle = grad; ctx.fillRect(0, 0, W, H);
        ctx.fillStyle = accent || '#0e7a4e'; ctx.fillRect(0, 0, W, 14);

        // Arabic text: wrap by measured width, shrink until it fits the box.
        const boxW = W - 160, boxTop = 60, boxBottom = 430;
        const words = String(arText).replace(/\s+/g, ' ').trim().slice(0, 600).split(' ');
        let size = 72, lines = [];
        for (; size >= 28; size -= 4) {
            ctx.font = `400 ${size}px ${quranFont}`;
            lines = [];
            let cur = '';
            for (const w of words) {
                const test = cur ? `${cur} ${w}` : w;
                if (cur && ctx.measureText(test).width > boxW) { lines.push(cur); cur = w; } else { cur = test; }
            }
            if (cur) lines.push(cur);
            if (lines.length * size * 1.75 <= boxBottom - boxTop) break;
        }
        const lh = size * 1.75;
        const startY = boxTop + ((boxBottom - boxTop) - lines.length * lh) / 2 + lh * 0.7;
        ctx.fillStyle = '#ffffff';
        ctx.direction = 'rtl'; ctx.textAlign = 'right'; ctx.textBaseline = 'alphabetic';
        ctx.font = `400 ${size}px ${quranFont}`;
        lines.forEach((ln, i) => ctx.fillText(ln, W - 80, startY + i * lh));

        ctx.direction = 'ltr'; ctx.textAlign = 'left';
        ctx.fillStyle = '#9fe3c3';
        ctx.font = '600 34px Cairo, sans-serif';
        ctx.fillText(title, 60, 510);
        ctx.font = '400 26px Cairo, sans-serif';
        ctx.fillStyle = '#7fb89d';
        ctx.fillText(url.length > 70 ? `${url.slice(0, 67)}…` : url, 60, 570);

        return await new Promise((resolve) => {
            try { canvas.toBlob((b) => resolve(b || null), 'image/png'); } catch { resolve(null); }
        });
    } catch {
        return null;
    }
}
const store = (k, v) => { try { const x = JSON.parse(localStorage.getItem(k)); return x === null || x === undefined ? v : x; } catch { return v; } };
const save = (k, v) => { try { localStorage.setItem(k, JSON.stringify(v)); } catch { /* private mode */ } };

let audio = null;
const getAudio = () => (audio = audio || new Audio());

const fetchJson = async (url) => {
    const res = await fetch(url, { credentials: 'omit' });
    if (!res.ok) throw new Error(`http ${res.status}`);
    return res.json();
};

/* ---- per-surah data loaders (cached) ---- */
async function loadFr(surahN, edition) {
    const key = `bh-fr-${edition}-${surahN}`;
    const hit = store(key, null);
    if (hit) return hit;
    try {
        const body = await fetchJson(`https://api.alquran.cloud/v1/surah/${surahN}/editions/${edition}`);
        const ed = (body.data || [])[0];
        const out = {};
        (ed?.ayahs || []).forEach((a) => { out[a.numberInSurah] = a.text; });
        if (Object.keys(out).length) { save(key, out); return out; }
    } catch { /* offline or unknown edition */ }
    // graceful fallback to Cherrier
    if (edition !== 'fr.hamidullah') return loadFr(surahN, 'fr.hamidullah');
    return {};
}

async function loadSimple(surahN) {
    const key = `bh-simple-${surahN}`;
    const hit = store(key, null);
    if (hit) return hit;
    try {
        const body = await fetchJson(`https://api.alquran.cloud/v1/surah/${surahN}/editions/quran-simple`);
        const ed = (body.data || [])[0];
        const out = {};
        (ed?.ayahs || []).forEach((a) => { out[a.numberInSurah] = a.text; });
        // alquran.cloud prepends the basmala to verse 1 (except surahs 1 and 9)
        if (surahN !== 1 && surahN !== 9 && out[1]) out[1] = out[1].split(/\s+/).slice(4).join(' ');
        if (Object.keys(out).length) { save(key, out); return out; }
    } catch { /* */ }
    return {};
}

async function loadTafsir(surahN) {
    const key = `bh-tafsir-${surahN}`;
    const hit = store(key, null);
    if (hit) return hit;
    try {
        const body = await fetchJson(`https://api.alquran.cloud/v1/surah/${surahN}/editions/ar.muyassar`);
        const ed = (body.data || [])[0];
        const out = {};
        (ed?.ayahs || []).forEach((a) => { out[a.numberInSurah] = a.text; });
        if (Object.keys(out).length) { save(key, out); return out; }
    } catch { /* */ }
    return {};
}

async function loadW2w(globalN, verseKey) {
    const key = `bh-w2w-${globalN}`;
    const hit = store(key, null);
    if (hit) return hit;
    const body = await fetchJson(`https://api.quran.com/api/v4/verses/by_key/${verseKey}?words=true&language=en&word_fields=text_uthmani`);
    const words = body.verse?.words;
    if (!words) throw new Error('no words');
    const out = words
        .filter((w) => w.char_type_name === 'word')
        .map((w) => ({
            ar: w.text_uthmani || w.text,
            gloss: [w.translation?.text, w.transliteration?.text ? `(${w.transliteration.text})` : ''].filter(Boolean).join(' '),
        }));
    save(key, out);
    return out;
}

/* ---- reader ---- */
function bhReader(surahN) {
    const reciters = cfg.quran?.audioBases || {};
    let repeatLeft = 0;
    let forceSeq = false;

    const notes = store(`bh-note-${surahN}`, {});

    const arTexts = {};
    document.querySelectorAll('.bh-ayah').forEach((el) => {
        const n = Number(el.dataset.ayah);
        const span = el.querySelector('.bh-ayah__ar');
        if (span) {
            const c = span.cloneNode(true);
            c.querySelectorAll('.bh-ayah__num').forEach((x) => x.remove());
            arTexts[n] = c.textContent.trim();
        }
    });
    const totalAyah = Object.keys(arTexts).length;

    return {
        surahN,
        reciter: store('bh-reciter', 'ar.alafasy'),
        frEdition: 'fr.hamidullah',
        showFr: store('bh-show-fr', true),
        simpleScript: store('bh-simple', false),
        tafsirOpen: false,
        speed: '1',
        repeatN: '0',
        sequential: store('bh-sequential', false),
        // reactive data (must live on the component for Alpine to re-render)
        frData: {},
        simpleData: {},
        tafsirData: {},
        w2wData: {},
        w2wErr: false,
        tafLoading: false,
        bm: store(`bh-bm-${surahN}`, {}),
        allPlaying: false,
        playingAyah: null,
        noteOpen: null,
        noteDraft: '',
        tafsirAyah: null,
        w2wAyah: null,
        _frPromise: null,
        _simplePromise: null,

        init() {
            window.__bhArTexts = arTexts;
            // cache Arabic text so the index full-text search works offline
            save(`bh-ar-${this.surahN}`, Object.keys(arTexts).map(Number).sort((a, b) => a - b).map((k) => arTexts[k]));
            this.$watch('reciter', (v) => save('bh-reciter', v));
            this.$watch('frEdition', (v) => { save('bh-fr-edition', v); this._frPromise = null; this.ensureFr(); });
            this.$watch('simpleScript', (v) => { save('bh-simple', v); if (v) this.ensureSimple(); });
            this.$watch('showFr', (v) => save('bh-show-fr', v));
            this.$watch('sequential', (v) => save('bh-sequential', v));
            this.$watch('speed', (v) => { if (audio) audio.playbackRate = Number(v) || 1; });
            this.ensureFr();
            if (this.simpleScript) this.ensureSimple();
            // resume scroll target (#a-N anchor is handled by the browser)
            const mark = () => this.markRead();
            window.addEventListener('beforeunload', mark);
            this.scrollTimer = setInterval(mark, 15000);
            window.addEventListener('beforeunload', () => clearInterval(this.scrollTimer));
        },
        thisSurah() { return this.surahN; },

        arText(n) {
            const t = arTexts[n] || '';
            const simple = this.simpleScript ? (this.simpleData[n] || '') : '';
            const inner = simple || t;
            return `${inner}<a class="bh-ayah__num" href="#a-${n}" id="a-${n}">${n}</a>`;
        },

        async ensureFr() {
            if (this._frPromise) return this._frPromise;
            this._frPromise = loadFr(this.surahN, this.frEdition)
                .then((data) => { this.frData = data || {}; })
                .catch(() => {});
            return this._frPromise;
        },

        frText(n) {
            if (!this.showFr) return '';
            return this.frData[n] || '';
        },

        async ensureSimple() {
            if (!this._simplePromise) {
                this._simplePromise = loadSimple(this.surahN).then((d) => { this.simpleData = d || {}; }).catch(() => {});
            }
            return this._simplePromise;
        },

        async playAyah(n, isRepeat = false) {
            const base = reciters[this.reciter];
            const el = document.querySelector(`.bh-ayah[data-ayah="${n}"]`);
            if (!base || !el) return;
            const a = getAudio();
            this.allPlaying = forceSeq ? true : this.allPlaying;
            this.playingAyah = n;
            el.classList.add('is-playing');
            a.playbackRate = Number(this.speed) || 1;
            const global = Number(el.dataset.global);
            a.src = base + global + '.mp3';
            a.onended = () => {
                el.classList.remove('is-playing');
                if (repeatLeft > 0) {
                    repeatLeft -= 1;
                    this.playAyah(n, true);
                    return;
                }
                const wantSeq = forceSeq || this.sequential;
                if (wantSeq && n < totalAyah) {
                    this.playAyah(n + 1);
                } else {
                    this.playingAyah = null;
                    this.allPlaying = false;
                    forceSeq = false;
                }
            };
            if (!isRepeat) repeatLeft = Number(this.repeatN) > 1 ? Number(this.repeatN) - 1 : 0;
            a.play().catch(() => {
                // offline / blocked: reset UI state
                el.classList.remove('is-playing');
                this.allPlaying = false;
                this.playingAyah = null;
                forceSeq = false;
            });
            this.markRead(n);
        },

        toggleAll() {
            if (this.allPlaying) { this.stopAll(); return; }
            forceSeq = true;
            this.playAyah(1);
        },

        stopAll() {
            forceSeq = false;
            this.allPlaying = false;
            const a = getAudio();
            a.onended = null;
            a.pause();
            this.playingAyah = null;
            document.querySelectorAll('.bh-ayah.is-playing').forEach((el) => el.classList.remove('is-playing'));
        },

        /* ---- bookmarks & notes ---- */
        isBookmarked(n) { return Boolean(this.bm[n]); },
        toggleBookmark(n) {
            const next = { ...this.bm };
            if (next[n]) delete next[n]; else next[n] = Date.now();
            this.bm = next;
            save(`bh-bm-${surahN}`, next);
        },
        bookmarksFor(surah) {
            if (surah !== this.surahN) return [];
            return Object.keys(this.bm).map(Number).sort((a, b) => a - b);
        },
        toggleNote(n) {
            this.noteOpen = this.noteOpen === n ? null : n;
            this.noteDraft = notes[n] || '';
        },
        saveNote(n) {
            notes[n] = this.noteDraft;
            save(`bh-note-${this.surahN}`, notes);
            this.noteOpen = null;
        },

        /* ---- tafsir & word-by-word (sync getters for x-html) ---- */
        tafsirHtml(n) {
            if (this.tafsirData[n]) return `<p dir="rtl" style="text-align:right">${String(this.tafsirData[n]).replace(/</g, '&lt;')}</p>`;
            if (this.tafLoading) return '…';
            return this.tafsirAyah === n && this.tafsirOpen
                ? `<p class="bh-muted">${lang('Tafsir indisponible hors ligne.', 'التفسير غير متاح دون اتصال.')}</p>`
                : '';
        },
        tafsirLoading() { return this.tafLoading; },
        async toggleTafsir(n) {
            this.tafsirAyah = this.tafsirAyah === n ? null : n;
            if (this.tafsirAyah) this.tafsirOpen = true;
            if (this.tafsirAyah && !this.tafsirData[n]) {
                this.tafLoading = true;
                this.tafsirData = { ...(await loadTafsir(this.surahN)) };
                this.tafLoading = false;
            }
        },
        w2wHtml(n) {
            const el = document.querySelector(`.bh-ayah[data-ayah="${n}"]`);
            const global = el ? Number(el.dataset.global) : null;
            if (!global || !this.w2wData[global]) return '';
            const esc = (x) => String(x == null ? '' : x).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
            return (this.w2wData[global] || [])
                .filter((w) => w.ar)
                .map((w) => `<span class="bh-w2w__w" title="${esc(w.gloss)}"><b>${esc(w.ar)}</b><small>${esc(w.gloss)}</small></span>`)
                .join(' ');
        },
        w2wError() { return this.w2wErr; },
        async toggleW2W(n) {
            this.w2wAyah = this.w2wAyah === n ? null : n;
            if (this.w2wAyah) {
                const el = document.querySelector(`.bh-ayah[data-ayah="${n}"]`);
                const global = el ? Number(el.dataset.global) : null;
                if (global && !this.w2wData[global]) {
                    try {
                        this.w2wData = { ...this.w2wData, [global]: await loadW2w(global, `${this.surahN}:${n}`) };
                        this.w2wErr = false;
                    } catch {
                        this.w2wErr = true;
                    }
                }
            }
        },

        /* ---- share ---- */
        async shareAyah(n) {
            const t = arTexts[n] || '';
            const title = lang(
                `Al-Quran — Sourate ${this.surahN}:${n}`,
                `القرآن — سورة ${this.surahN}:${n}`
            );
            const url = `${location.origin}${location.pathname}#a-${n}`;
            const text = shareText(`${title} — ${clip(t, 120)}`, url);
            const fileName = `quran-${this.surahN}-${n}.png`;

            // 1. Build the PNG card first (never throws; null on failure).
            const blob = await buildAyahCard(t, title, url, cfg.accent);

            // 2. Phones: share the image itself when the platform supports files.
            if (blob && typeof navigator.share === 'function' && typeof navigator.canShare === 'function') {
                try {
                    const file = new File([blob], 'ayah.png', { type: 'image/png' });
                    if (navigator.canShare({ files: [file] })) {
                        await navigator.share({ files: [file], title, text, url });
                        return;
                    }
                } catch (e) {
                    if (e && e.name === 'AbortError') return; // user dismissed the sheet
                    /* fall through to text+link */
                }
            }
            // 3. Text + link share.
            if (typeof navigator.share === 'function') {
                try { await navigator.share({ title, text, url }); } catch { /* dismissed */ }
                return;
            }
            // 4. Desktop: download the PNG.
            if (!blob) return;
            try {
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                a.remove();
                setTimeout(() => URL.revokeObjectURL(a.href), 4000);
            } catch { /* */ }
        },

        /* ---- last read (resume) ---- */
        markRead(n) {
            const ayah = n || this.visibleAyah();
            if (!ayah) return;
            const el = document.querySelector(`.bh-ayah[data-ayah="${ayah}"]`);
            save('bh-last-read', {
                surah: this.surahN,
                ayah,
                global: el ? Number(el.dataset.global) : null,
                ts: Date.now(),
            });
        },
        visibleAyah() {
            const els = [...document.querySelectorAll('.bh-ayah')];
            if (!els.length) return null;
            const mid = window.innerHeight / 2;
            let best = null; let bestDist = Infinity;
            for (const el of els) {
                const r = el.getBoundingClientRect();
                const dist = Math.abs(r.top + r.height / 2 - mid);
                if (r.top < window.innerHeight && r.bottom > 0 && dist < bestDist) {
                    bestDist = dist;
                    best = Number(el.dataset.ayah);
                }
            }
            return best;
        },
    };
}

/* ---- plan + quiz ---- */
function bhPlan() {
    const plan = store('bh-plan', null) || { days: 30, start: new Date().toISOString() };
    const reader = document.querySelector('[data-bh-reader]');
    const arTexts = reader
        ? [...reader.querySelectorAll('.bh-ayah')].map((el) => {
            const c = el.querySelector('.bh-ayah__ar')?.cloneNode(true);
            if (!c) return '';
            c.querySelectorAll('.bh-ayah__num').forEach((x) => x.remove());
            return c.textContent.trim();
        })
        : [];
    const surahN = Number(reader?.dataset.surah || 1);
    const questions = quizQuestions(arTexts, 5, surahN);
    return {
        planDays: String(plan.days),
        _plan: null,
        quizActive: false,
        quizI: 0,
        quizAnswered: false,
        quizCorrect: 0,
        questions,
        init() {},
        setPlan() {
            this._plan = planSchedule(this.planDays, new Date());
            save('bh-plan', { days: this.planDays, start: new Date().toISOString() });
            this.$nextTick(() => {});
        },
        pct() {
            const last = store('bh-last-read', null);
            const p = planSchedule(this.planDays, new Date());
            return planProgress(last?.global || 0, p);
        },
        planStatus() {
            const last = store('bh-last-read', null);
            const p = planSchedule(this.planDays, new Date());
            if (!last?.global) return lang('Commencez à lire — votre progression suivra automatiquement.', 'ابدأ القراءة — ستُتبع تقدمك تلقائيًا.');
            const day = planDayOf(last.global, p);
            return lang(
                `Jour ${day} sur ${p.days} — ${this.pct()} % du Coran.`,
                `اليوم ${day} من ${p.days} — ${this.pct()}٪ من القرآن.`
            );
        },
        openQuiz() {
            this.quizActive = true;
            this.quizI = 0;
            this.quizAnswered = false;
            this.quizCorrect = 0;
        },
        quizTotal() { return this.questions.length || 1; },
        quizIndex() { return Math.min(this.quizI + 1, this.questions.length); },
        quizQuestion() {
            const q = this.questions[this.quizI];
            return q
                ? lang(`Quel verset suit la ${q.pos}e ?`, `ما الآية التي تلي الآية ${q.pos}؟`) + ' ' + clip(arTexts[q.pos - 1], 90)
                : lang('Cette sourate est trop courte pour un quiz.', 'هذه السورة قصيرة جدًا للاختبار.');
        },
        quizOptions() {
            const q = this.questions[this.quizI];
            return q ? q.options : [];
        },
        quizPick(opt) {
            if (this.quizAnswered) return;
            const q = this.questions[this.quizI];
            if (!q) return;
            this._pick = opt;
            this.quizAnswered = true;
            if (q.options[q.answer] === opt) this.quizCorrect += 1;
            this.$nextTick(() => {});
        },
        quizResult() {
            if (!this.quizAnswered) return '';
            const q = this.questions[this.quizI];
            if (!q) return '';
            const ok = q.options[q.answer] === this._pick;
            return ok ? lang('✓ Correct !', '✓ صحيح!') : lang(`✗ Le verset suivant est : ${clip(q.options[q.answer], 60)}`, `✗ الآية التالية: ${clip(q.options[q.answer], 60)}`);
        },
        quizDone() { return this.quizAnswered; },
        nextQuiz() {
            this.quizI += 1;
            this.quizAnswered = false;
            if (this.quizI >= this.questions.length) this.quizActive = false;
            this.$nextTick(() => {});
        },
    };
}

Alpine.data('bhReader', (n) => bhReader(n));
Alpine.data('bhPlan', bhPlan);
