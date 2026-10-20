# Architecture

## Vue d'ensemble

```
┌──────────────────────────── cPanel / Docker ─────────────────────────────┐
│  Apache + PHP 8.1+  +  MariaDB                                           │
│  ┌────────────── WordPress (cœur épinglé, sources.json) ──────────────┐ │
│  │ theme  baaboul-houda   (HTML/CSS/JS pré-buildés, commités)         │ │
│  │ plugin baaboul-houda-core                                          │ │
│  │   i18n FR/AR · CPTs · réglages · live · radio · grille · prières   │ │
│  │   coran · 99 noms · aadhkar · hadiths · contact · SEO · PWA        │ │
│  │ données: data/quran.json (114 sourates Uthmani), names99, adhkar…  │ │
│  └─────────────────────────────────────────────────────────────────────┘ │
└───────────────────────────────────────────────────────────────────────────┘
        ▲                                   ▲
        │ embeds (YouTube/Facebook/HLS)     │ API (runtime, cache côté client)
   fournisseurs de flux           alquran.cloud (traduction FR) · Aladhan (prières)
```

## Pourquoi WordPress + thème/plugin custom (et pas un CMS headless)

- **cPanel est l'hébergement cible** (choix du client) : WordPress y est
  natif (PHP+MySQL), mis en place en 15 min, mis à jour sans développeur.
- **L'équipe du client est non-technique** : l'admin WP est l'interface de
  mise à jour. Tout le domaine métier (direct, radio, grille, épisodes,
  versions arabe) est modélisé dans un **plugin unique** `baaboul-houda-core`
  → zéro plugin tiers de contenu à maintenir (pas d'ACF, pas de Polylang,
  pas de Contact Form 7) = moins de failles de sécurité, moins de conflits.
- **Docker reste le mode de test/dev** : `docker compose up` = site complet ;
  l'image Docker embarque le cœur WP épinglé.

## Bilingue FR/AR sans plugin

- Le français est la langue **native** WP (titres, contenus, posts).
- L'arabe est stocké en meta compagnons (`_bh_title_ar`, `_bh_content_ar`,
  `_bh_excerpt_ar`) sur chaque page/post/CPT — la méta-boîte « Version arabe »
  est affichée sur toutes les écrans d'édition.
- Routage : `?lang=ar` partout + URLs propretes `/ar/{section}/` et
  `/coran/{n}/` (rewrite rules du plugin). Le switcher du top-bar génère
  toujours le lien miroir de la page courante ; `hreflang` FR/AR + x-default
  dans le `<head>`.

## Live TV : un champ, trois plateformes

`Réglages > En direct` = { en direct (bool), plateforme (youtube|facebook|hls|off),
URL, titre FR/AR }. Le template `/direct/` branché sur `bh_live_embed_html()` :
- youtube → iframe `youtube.com/embed/{id}`
- facebook → iframe `facebook.com/plugins/video.php?href=…`
- hls → `<video>` + hls.js (bundle dans `assets/dist/js/main.js`)

## Coran : hors-ligne d'abord

- Le texte Uthmani complet (6236 versets, riyāda Hafs ʿan ʿĀṣim) est **embarqué**
  dans `data/quran.json` (généré par `build/quran/build.mjs` à partir de
  sources publiques Tanzil/QCF épinglées). Fonctionne sans connexion.
- Traduction française : chargée à la demande par sourate depuis
  alquran.cloud (CC-BY), mise en cache `localStorage`.
- Audio : CDN islamich.network (par numéro global de verset), 3 récitateurs.

## Données islamiques : sources et licences

| Jeu | Source | Licence |
|---|---|---|
| Texte coranique | github.com/AlaaJabre/quran-hafs-uthmani (Tanzil/QCF) | Public domain |
| 99 noms | github.com/KabDeveloper/99-Names-Of-Allah | source citée dans le JSON |
| Aadhkar (Hisn al-Muslim) | github.com/YousefAsalya/Islamic-Pro-azkar-API | MIT (attribuée) |
| 42 hadiths Nawawi | github.com/uthumany/nawawi-40-hadiths (textes arabes uniquement — les traductions EN sont © Salaf.Com et non redistribuées) | domaine public (textes classiques) |

## Tests

- **CI (GitHub Actions)** : `php -l` dans un conteneur php:8.3-cli (vrai PHP),
  `npm test` (lint JS de tous les PHP via php-parser, vitest, validation
  quran.json/adhkar/noms, intégrité du release), `git diff --exit-code` sur
  les dist/ (le zip cPanel ne doit jamais contenir d'assets périmés),
  **build de l'image Docker**.
- **Local** : `npm test` (tout sauf le build Docker, qui passe en CI),
  `docker compose up` pour le rendu réel.

## Arborescence

```
baboolhouda/
├── site/                     ← racine WordPress déployable (le zip cPanel)
│   ├── wp-content/themes/baaboul-houda/
│   ├── wp-content/plugins/baaboul-houda-core/
│   └── (wp core épinglé — scripts/vendor-core.sh, git-ignoré)
├── build/                    ← pipelines de données (coran, islam, icônes, lint)
├── scripts/                  ← vendor-core, build-release, test-local
├── docs/                     ← cpanel-deploy, content-guide, roadmap, ce fichier
├── .github/workflows/ci.yml  ← CI
├── Dockerfile, docker-compose.yml, .env.example
└── sources.json              ← versions épinglées (WP core, coran, dépendances)
```
