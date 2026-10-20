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
- [x] CI : php -l (vrai PHP), tests unitaires, validation des données,
      intégrité du release, build Docker

## Prochaines étapes (phase 2)
- [ ] Newsletter (service type Mailchimp/Brevo + formulaire)
- [ ] Analytique (Plausible auto-hébergé ou GA4 — champ prêt dans les réglages)
- [ ] Flux Facebook/YouTube automatique : importer la liste des vidéos de la
      chaîne dans « Replays » (script d'import, API YouTube Data)
- [ ] Compteur de lectures / likes par épisode
- [ ] Page « Cours en ligne » (la chaîne propose des cours — liste + horaires)
- [ ] Publicité locale : zone pub encadrée (la chaîne vend de la publicité)
- [ ] Mode sombre
- [ ] Traduction FR des hadiths Nawawi (par l'équipe éditoriale)
- [ ] App mobile PWA : notifications de direct (si hébergement compatible)

## Mesures suggérées (phase 3)
- [ ] Sous-domaine coran. (mushaf dédié, plus lourd : audio par verset sync)
- [ ] Application de prière par mosquée (carte des mosquées de Ouagadougou)
- [ ] Chaîne audio séparée pour femmes (groupe WhatsApp mentionné par la chaîne)
