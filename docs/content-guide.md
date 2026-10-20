# Guide de mise à jour du site — TV Baaboul Houda

Tout se fait dans **wp-admin**. Aucun développeur n'est nécessaire pour les
opérations courantes.

## 1. 🔴 Mettre la TV « EN DIRECT » (ou la couper)

Menu **TV Baaboul Houda > Réglages du site > En direct** :

1. Cocher **« La chaîne est en direct »**.
2. Choisir la plateforme :
   - **YouTube** → coller l'URL de la vidéo en cours (celle du direct),
   - **Facebook** → coller l'URL de la vidéo de la page,
   - **Flux HLS** → URL `.m3u8` de votre hébergeur vidéo,
   - **Hors ligne** → le player est masqué.
3. Écrire le titre (FR et AR) et **Enregistrer**.

Le badge rouge **● EN DIRECT** apparaît immédiatement dans le menu et
l'accueil affiche le player. Pour finir : décocher la case → Enregistrer.

> Astuce : pour YouTube, copiez le lien depuis *Partager > Copier le lien* de
> la vidéo en cours.

### Publication automatique sur Facebook (optionnel)

Quand la chaîne passe de « hors antenne » à « en direct », le site peut publier
tout seul un message « 🔴 EN DIRECT — titre — lien vers /direct/ » sur la page Facebook.

1. Créer une application sur *developers.facebook.com* et générer un **jeton d'accès
   de la Page** (permissions `pages_show_list` et `pages_manage_posts`), idéalement
   un jeton longue durée.
2. Dans **Réglages du site > Publication automatique Facebook** : cocher
   *Activer*, renseigner le **Page ID** (numéro de la page) et le **jeton**.
3. Enregistrer. La publication a lieu uniquement lors du passage 0 → 1 de
   « La chaîne est en direct » (pas à chaque enregistrement). Si l'URL de la
   diffusion n'a pas changé depuis la dernière publication, rien n'est reposté.

> Un échec Facebook (jeton expiré, permission manquante) n'empêche jamais
> l'enregistrement des réglages ; il est simplement silencieux. Si les posts
> n'apparaissent pas, régénérer le jeton.

## 2. 📻 Radio

**Réglages du site > Radio** : cocher *Activer le player radio* et coller
l'URL du flux MP3/AAC (ou `.m3u8`). Une barre verte apparaît en bas du site
sur toutes les pages. Couper : décocher.

## 3. 🗓️ Grille des programmes

**Réglages du site > Grille des programmes** : chaque ligne = un créneau
(jour, heure de début, heure de fin, programme lié). Les programmes liés
sont ceux créés dans le menu **Programmes** (voir ci-dessous).
L'ordre ne compte pas — le site trie par heure.

## 4. 📺 Programmes (émissions)

Menu **Programmes > Ajouter** :
- titre (FR) + image (logo de l'émission),
- description (FR),
- dans la zone **« Version arabe »** : titre et description en arabe
  (laisser vide = la version FR s'affiche aussi sous `/ar/`).
Catégories (religion, culture, éducation…) via la méta-boîte Catégorie.

## 5. ▶️ Replays (épisodes)

Menu **Épisodes (replays) > Ajouter** :
- titre, description (FR + AR),
- **Source** : YouTube / Facebook / Vidéo auto-hébergée (URL `.mp4`) /
  Audio seul (URL `.mp3`), + URL,
- **Programme parent** : l'émission dont c'est un replay,
- **Date de diffusion**.
L'image miniature s'affiche sur la carte de l'épisode (utilisez la vignette
YouTube si possible).

## 6. 🗞️ Actualités

Menu **Articles** : écrire l'article en FR, remplir la version arabe dans la
zone *Version arabe*. Image mise en avant = image de partage Facebook/WhatsApp.

## 7. 🎙️ Animateurs / 📅 Événements

- **Animateurs** : photo + biographie (FR + AR).
- **Événements** : titre + description. **La date de publication de
  l'événement = la date de l'événement** (bouton Modifier la date).

## 8. 🕌 Prières

**Réglages du site > Prières** : ville par défaut (Ouagadougou), méthode de
calcul (Muslim World League par défaut — l'usage en Afrique de l'Ouest),
et la liste des villes proposées (séparées par des virgules).
Les horaires proviennent de l'API Aladhan (gratuite, sans clé) ; si le
service est indisponible, des horaires indicatifs hors-ligne sont affichés
et étiquetés comme tels.

## 9. ✉️ Contact

**Réglages du site > Contact** : email de réception des messages (les
messages du formulaire vous arrivent par email) + numéro WhatsApp affiché.

## 10. 🎨 Identité

**Réglages du site > Identité** : logo (image carrée ou horizontale),
couleur d'accent, URL Facebook/YouTube, image de partage OG (1200×630).

## Raccourcis URL utiles

| Page | URL |
|---|---|
| Accueil FR / AR | `/` · `/ar/` |
| TV en direct | `/direct/` · `/ar/direct/` |
| Radio | `/radio/` |
| Programmes + grille | `/programmes/` |
| Replays | `/emissions/` (filtre : `/emissions/?programme=ID`) |
| Coran | `/coran/` · sourate : `/coran/36/` (Ya-Sin) |
| Prières | `/prieres/` |
| Actualités / Événements / Animateurs / Contact | `/actualites/` · `/evenements/` · `/animateurs/` · `/contact/` |
| Invocations (Hisn al-Muslim) / 99 noms d’Allah | `/adhkar/` · `/noms-allah/` (AR : `/ar/adhkar/` · `/ar/noms-allah/`) |
| Sitemap | `/sitemap.xml` |
