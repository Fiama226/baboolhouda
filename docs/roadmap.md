# Roadmap — TV Baaboul Houda

## Livré (v1)
- [x] Site bilingue FR/AR (RTL complet, langue switchable, hreflang)
- [x] TV en direct commutable YouTube / Facebook / HLS (1 champ admin)
- [x] Radio 24/7 (player persistant + page dédiée, flux MP3/AAC/HLS)
- [x] Programmes (CPT) + grille hebdomadaire EPG (7 jours)
- [x] Replays multi-sources (YouTube, Facebook, fichier MP3/MP4)
- [x] Animateurs, Événements, Actualités, Contact (formulaire + WhatsApp)
- [x] Coran : 114 sourates (Uthmani, hors-ligne), recherche, traduction FR
      (alquran.cloud), audio 3 récitateurs, verset du jour
- [x] Heures de prière (Aladhan, ville, date hégirienne, calendrier mensuel,
      fallback hors-ligne Ouagadougou)
- [x] 99 noms d'Allah, Aadhkar (Hisn al-Muslim, 132 sections), 42 hadiths Nawawi
- [x] PWA installable (manifest + service worker + icônes générées)
- [x] SEO maison (title/meta/OG/hreflang/JSON-LD BroadcastChannel + VideoObject,
      sitemap FR+AR)
- [x] Docker (image complète + compose local) et déploiement cPanel (zip)
- [x] CI GitHub Actions (`.github/workflows/ci.yml`) : php -l (vrai PHP), tests
      unitaires, validation des données, intégrité du release, fraîcheur de
      `assets/dist`, build Docker
- [x] Docker : installation automatique (WP + thème + plugin activés, démo via `SEED_DEMO`)

## Livré (v2) — voir [features.md](features.md) pour le détail ligne par ligne
Newsletter (table + export CSV), analytique (snippet + compteur léger), import
YouTube (`scripts/yt-import.mjs`), compteur de vues / tendances, mode sombre,
publicité, Facebook auto-post EN DIRECT, widget heures de prière, etc.

## Reste à faire / partiel
- [ ] Mushaf 604 pages (aujourd'hui : navigation par 30 juz)
- [ ] Pré-cache hors-ligne des sourates (service worker : `APP_SHELL` vide)
- [ ] Compteur de spectateurs simultanés (aujourd'hui : vues de pages)
- [ ] Calendrier hégirien mensuel (aujourd'hui : convertisseur + événements)
- [ ] Mot par mot : audio par mot (le glossaire au toucher est fait)
- [ ] Carte de partage verset : générée aussi quand le Web Share natif existe
- [ ] Traduction FR des hadiths Nawawi (par l'équipe éditoriale)
- [ ] Notifications push de direct (nécessite un service push, hors hébergement mutualisé)

## Mesures suggérées (phase 3)
- [ ] Sous-domaine coran. (mushaf dédié, plus lourd : audio par verset sync)
- [ ] Application de prière par mosquée (carte des mosquées de Ouagadougou)
- [ ] Chaîne audio séparée pour femmes (groupe WhatsApp mentionné par la chaîne)
