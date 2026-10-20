# Expansion checklist (80-item competitive audit)

Status: ✅ done · ⚠️ partial (voir note) · ⬜ pending · ❌ excluded by client
Client exclusions: 4, 40, 45, 46, 48, 69, 78, 79, 80
Last updated: 2026-10-20 (v2 delivery, statuts vérifiés contre le code)

## A. Live TV & radio
| # | Feature | Status |
|---|---------|--------|
| 1 | Video quality selector (HLS levels) | ✅ `__bhLiveHls.setQuality`, select in player |
| 2 | "Next program" live countdown in player | ✅ `bhLive.tick()` (20 s refresh) |
| 3 | 7-day EPG grid with "now" highlight | ✅ schedule grid + current-slot highlight |
| 5 | Radio "Last played" list | ✅ radio history (admin field) |
| 6 | Radio schedule (on-air now/next for radio) | ✅ "À l'antenne / À suivre" card |
| 7 | Radio podcast archive + RSS feed | ✅ podcast section; core WP `/feed/` |
| 8 | "Just ended" banner after live slot | ✅ `justTitle/justUrl` in player |
| 9 | YouTube channel → Replays auto-import (script) | ✅ `scripts/yt-import.mjs` (API key + app password) |
| 10 | Live viewer counter (server-side, graceful) | ⚠️ compteur de **vues de pages** (`_bh_views`) + tendances ; pas de compteur de spectateurs simultanés |
| 11 | "Watch on phone" QR code on live page | ✅ lazy qrcode, modal on live page |
| 12 | Second channel "Coran 24/7" (TV1/TV2) | ✅ `bh_live_channel2`, TV1/TV2 tabs |
| 13 | 24h catch-up → "This week's replay" aggregate | ✅ "Cette semaine" filter on replays |

## B. Quran
| # | Feature | Status |
|---|---------|--------|
| 14 | Multiple FR translations (2–4 selectable) | ✅ 3 editions (Cherrier, Berque, Al-Que) |
| 15 | Verse share card (PNG, FB/WhatsApp) | ✅ canvas 1200×630 → PNG par verset (`shareAyah`/`buildAyahCard`, quran.js) ; partagé comme fichier via Web Share sur mobile (`navigator.canShare({files})`), sinon texte+lien, et téléchargement du PNG sur ordinateur |
| 16 | Word-by-word view (tap word) | ✅ w2w via api.quran.com, cached, graceful |
| 17 | Tap a word to hear it | ⬜ needs per-word audio source (gloss on tap done) |
| 18 | Tafsir per verse (alquran.cloud runtime) | ✅ per-ayah tafsir, cached |
| 19 | Mushaf mode (604 pages) | ⚠️ navigation par 30 juz uniquement ; pas de pagination 604 pages du mushaf |
| 20 | Audio sync (per-ayah auto-advance + highlight) | ✅ sequential play + `is-playing` highlight |
| 21 | Reading plan tracker (30/15/7-day + progress) | ✅ `bhPlan`, localStorage |
| 22 | Resume reading (last verse) | ✅ `bh-last-read` + "Reprendre ma lecture" |
| 23 | Bookmarks + notes per verse | ✅ per-surah, persisted |
| 24 | Repeat verse N times | ✅ 1/3/5/10× |
| 25 | Full-text search (AR offline + FR) | ✅ offline index (quran.json) + FR API |
| 26 | Recitation speed control | ✅ 0.75–1.5× |
| 27 | Memorization quiz | ✅ deterministic MCQ over surah |
| 28 | Hijri calendar page (month + events) | ⚠️ page dates : convertisseur + événements/jeûnes à venir ; pas de grille mensuelle |
| 29 | Script modes (Uthmani / simplified) | ✅ Uthmani + simplified toggle |

## C. Prayer & tools
| # | Feature | Status |
|---|---------|--------|
| 30 | Qibla compass (device + static) | ✅ device orientation + per-city static bearing |
| 31 | Zakat calculator | ✅ |
| 32 | Hijri ↔ Gregorian converter | ✅ Intl Umm-al-Qura + deterministic inverse |
| 33 | Digital tasbih counter | ✅ phrases, rounds, persistence, haptics |
| 34 | Prayer PWA notifications | ✅ Web Notification at prayer time (+ adhan audio) |
| 35 | Adhan audio at prayer time | ✅ `initAdhan` (20 s poll, slot-dedup) |
| 36 | Ramadan dashboard (imsak/iftar/tarawih/seerah) | ⚠️ compte à rebours imsak/iftar OK ; seerah du jour désactivée en attente de relecture éditoriale |
| 37 | Fasting calendar (white months, Ashura…) | ✅ `upcomingFasts` (Aïd, Ashura, Arafah, 13-14-15) |
| 38 | Next Islamic holiday countdown | ✅ next-event card |
| 39 | Mosque list with map | ✅ curated list + map links |
| 41 | Embeddable prayer-time widget | ✅ `/wp-json/bh/v1/prayer-widget.js` snippet |
| 42 | Wudu step guide | ✅ 7 steps |
| 43 | Name of Allah of the day | ✅ 99-names rotation |
| 44 | Hadith of the day | ✅ hadiths rotation |
| 44a | Page « Invocations (Adhkar) » (`/adhkar/`) | ✅ Hisn al-Muslim : 132 sections, texte arabe + répétitions, recherche, copie ; source MIT citée en pied de page |
| 44b | Page « Les 99 noms d’Allah » (`/noms-allah/`) | ✅ 99 noms : arabe, translittération, sens FR + commentaire, recherche |

## D. Learning & content
| # | Feature | Status |
|---|---------|--------|
| 47 | Podcast section (+RSS) | ✅ podcast page + native feed |
| 49 | Fatwa / Q&A archive (CPT) | ✅ `fatwa` CPT + archive + single |
| 50 | "Ask a scholar" box | ✅ form → moderated CPT |
| 51 | Scholar profiles (videos by presenter) | ✅ single-animateur with "ses émissions" |
| 52 | Topic library (tag filters) | ✅ `sujet_episod` chip filter on replays |
| 53 | Kids section | ✅ curated kids page |
| 54 | Seasonal pages (Ramadan/Eid/Muharram) | ⚠️ dashboard Ramadan ; seerah du jour désactivée (relecture éditoriale) |
| 55 | Photo galleries (CPT) | ✅ `galerie` CPT + single |
| 56 | Transcripts (episode meta + display) | ✅ `_bh_transcript` + display + toggle |
| 57 | Related articles + reading time + byline | ✅ byline, reading time, trending sidebar |
| 58 | Most-read / trending (view counter) | ✅ `bh_trending`, news sidebar + related |
| 59 | Live search suggest (autocomplete) | ✅ header suggest via REST `/bh/v1/search` |
| 60 | Breadcrumbs + color-coded categories | ✅ breadcrumbs on section pages + category labels |
| 61 | Dark mode | ✅ `.bh-dark` theme, persisted toggle |
| 62 | Social share buttons | ✅ FB/WA/X + canvas share cards |

## E. Community, money & social proof
| # | Feature | Status |
|---|---------|--------|
| 63 | Donations page (Orange Money + bank) | ✅ |
| 64 | Ads & sponsorship page (media kit) | ✅ |
| 65 | Testimonials (CPT) | ✅ `temoignage` CPT + single |
| 66 | Facebook page live feed | ✅ FB Page Plugin embed (À propos) |
| 67 | YouTube channel feed | ✅ channel card + auto-import script (#9) |
| 68 | Floating WhatsApp button | ✅ |
| 70 | Viewer messages wall (CPT, moderated) | ✅ `message` CPT, hold-by-default |
| 71 | Press kit | ✅ page |
| 72 | Newsletter (DB list + admin export) | ✅ DB table + REST + CSV export |

## F. Performance & growth
| # | Feature | Status |
|---|---------|--------|
| 73 | Bundle split (lazy hls.js/howler/quran) | ✅ main.js 19 kB; hls/howler/qrcode lazy chunks |
| 74 | PWA offline Quran | ⚠️ SW : pages visitées (réseau d'abord) + assets en cache ; pas de pré-cache des 114 sourates (`APP_SHELL` vide) → hors-ligne seulement pour les sourates déjà ouvertes |
| 75 | Live alert ("we're live" toast via status poll) | ✅ `initLiveToast` polls REST status |
| 76 | Auto-post "EN DIRECT" to Facebook | ✅ `includes/community.php` : passage `live_enabled` 0→1 + Page ID + jeton (Réglages > Publication automatique Facebook) → POST Graph API `/{page}/feed` (non bloquant, jamais fatal) |
| 77 | Analytics (GA4/Plausible snippet) | ✅ settings snippet + light page-view counter |

## Excluded by client
| # | Feature |
|---|---------|
| 4 | "Remind me" button |
| 40 | Halal directory |
| 45 | Courses section |
| 46 | Course registration |
| 48 | IslamCast shorts |
| 69 | Event registration |
| 78 | Cloudflare CDN (ops) |
| 79 | Cookie consent banner |
| 80 | Local languages (Mooré/Dioula/Fulfudé) |
