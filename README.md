# TV Baaboul Houda — قناة باب الهدى

Site officiel de la chaîne **TV Baaboul Houda** (TV & radio islamiques, Ouagadougou — Burkina Faso).
Bilingue **français / arabe** (RTL complet), pensé pour une mise à jour sans développeur et un déploiement **cPanel**, avec un workflow de développement **Docker-first**.

> Brief complet : [`guide.txt`](guide.txt) · Architecture : [`docs/architecture.md`](docs/architecture.md) · Roadmap : [`docs/roadmap.md`](docs/roadmap.md)

## Démarrage rapide

### Dev / preview local (Docker)
```bash
docker compose up -d
# → http://localhost:8080  (site) · http://localhost:8080/wp-admin (admin)
```
Le site **s'installe tout seul** au premier `docker compose up` (service one-shot
`wpcli`) : WordPress est installé, le plugin `baaboul-houda-core` puis le thème
`baaboul-houda` sont activés, les permaliens sont réglés en `/%postname%/` et les
pages de section sont créées. Il n'y a **pas d'installateur WordPress à parcourir**.

- **Identifiants admin** : `admin` / `admin` par défaut (variables `WP_ADMIN_USER`,
  `WP_ADMIN_PASSWORD`, `WP_ADMIN_EMAIL`). À changer pour tout usage non local.
- **Contenu de démo** : programmes, replays, actualités, grille… sont injectés par
  `scripts/seed-demo.sh` (idempotent, **dev uniquement**). Désactiver avec
  `SEED_DEMO=0`, forcer un re-seed avec `FORCE=1`.
- **Port** : `HTTP_PORT` (8080 par défaut). Si 8080 est occupé, mettre par exemple
  `HTTP_PORT=8081` dans un fichier `.env` (voir `.env.example`) ; l'URL du site
  suit ce port.
- **Édition en direct** : le thème et le plugin sont montés depuis le dépôt
  (`./site/wp-content/themes/baaboul-houda` et `./site/wp-content/plugins/baaboul-houda-core`
  → `/var/www/html/wp-content/...`). Une modification PHP/CSS/JS est visible
  immédiatement (recharger la page) ; après un changement dans `assets-src/`,
  relancer `npm run build` (les assets buildés `assets/dist/` sont committés).
- **Repartir de zéro** : `docker compose down -v` (supprime la base et le volume WordPress).

### Tests (sans Docker)
```bash
npm install
npm test          # lint PHP (JS), tests unitaires, validation des données, intégrité release
bash scripts/test-local.sh
```
La CI GitHub Actions ([`.github/workflows/ci.yml`](.github/workflows/ci.yml)) exécute à
chaque push : `php -l` réel (image `php:8.3-cli`) sur `site/wp-content`, `npm ci && npm test`,
la vérification que `npm run build` ne modifie pas `assets/dist` (committé), et `docker build .`.

### Déploiement cPanel
```bash
npm install && npm run release
# → release/tv-baaboul-houda-release.zip  (WordPress complet : cœur + thème + plugins)
```
Puis suivre [`docs/cpanel-deploy.md`](docs/cpanel-deploy.md) (~15 min : base MySQL, upload, installateur, permalinks, SSL).

### Guide d'usage (équipe du client)
[`docs/content-guide.md`](docs/content-guide.md) — comment passer **EN DIRECT** (1 champ), brancher la radio, remplir la grille, publier des replays, articles FR+AR…

## Fonctionnalités (v1)

| | |
|---|---|
| 🔴 **TV en direct** | Player commutable YouTube / Facebook / HLS depuis l'admin (badge ● EN DIRECT) |
| 📻 **Radio 24/7** | Flux MP3/AAC/HLS, barre persistante + page dédiée |
| 🗓️ **Programmes** | Émissions (CPT) + grille hebdomadaire type EPG (7 jours) |
| ▶️ **Replays** | YouTube, Facebook, fichiers MP3/MP4 auto-hébergés, filtres par programme |
| 📖 **Coran** | 114 sourates Uthmani hors-ligne, recherche, traduction FR (alquran.cloud), 3 récitateurs, verset du jour |
| 🕌 **Prières** | Aladhan (ville, date hégirienne, calendrier mensuel, fallback hors-ligne Ouagadougou) |
| 📿 **Contenu islamique** | 99 noms d'Allah, Aadhkar (Hisn al-Muslim, 132 sections), 42 hadiths d'al-Nawawī |
| 🗞️ **Médias** | Actualités, Événements, Animateurs, Contact (email + WhatsApp) |
| 🌍 **FR/AR** | `/` + `/ar/…`, RTL, hreflang, switcher sur toutes les pages |
| 📱 **PWA** | Installable (manifest + service worker + icônes générées) |
| 🔍 **SEO** | Title/meta/OG/JSON-LD (BroadcastChannel, VideoObject), sitemap FR+AR |

## Structure

```
site/                      ← racine WordPress déployable (zip cPanel)
  wp-content/themes/baaboul-houda/       thème custom (assets pré-buildés)
  wp-content/plugins/baaboul-houda-core/ plugin métier (i18n, live, radio, coran…)
build/                     pipelines de données (coran, islamiques, icônes, lint)
scripts/                   vendor-core.sh · build-release.sh · test-local.sh
docs/                      déploiement cPanel · guide contenu · roadmap · architecture
Dockerfile + docker-compose.yml          dev/preview + image de production
sources.json               versions épinglées (WP core, données, dépendances)
```

## Versions épinglées ([sources.json](sources.json))

- WordPress core **7.1.3** (miroir officiel GitHub)
- Coran : Hafs ʿan ʿĀṣim, moucharaf de Médine (Tanzil/QCF, public domain)
- Alpine.js / hls.js / howler.js via npm (bundlés, aucun CDN runtime hormis
  Google Fonts et les plateformes de flux)
