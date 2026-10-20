# Déploiement cPanel — TV Baaboul Houda

Deux méthodes. La **méthode A (zip)** est la plus simple ; la **méthode B (git)**
convient si l'hébergeur autorise l'accès SSH/git.

Prérequis : un compte cPanel avec **PHP 8.1+** (cPanel > "Select PHP Version"),
**MySQL/MariaDB** et **1 Go de disque**.

---

## Méthode A — Upload du zip (recommandé)

1. **Construire le zip** (en local, une fois) :
   ```bash
   npm install
   npm run release
   # → release/tv-baaboul-houda-release.zip
   ```
2. **Créer la base de données** :
   cPanel > *Bases de données MySQL* > créer base + utilisateur, mot de passe
   fort, puis **ajouter l'utilisateur à la base (privilèges totaux)**.
3. **Uploader** le zip via *Gestionnaire de fichiers* dans `public_html`
   (ou un sous-domaine : `tv.baaboulhouda.bf/public_html`), puis **extraire**.
4. **Configurer `wp-config.php`** :
   - renommer `wp-config-sample.php` en `wp-config.php`,
   - renseigner `DB_NAME`, `DB_USER`, `DB_PASSWORD`,
   - générer les clés : <https://api.wordpress.org/secret-key/1.1/salt/> (coller).
5. **Installer** : ouvrir le site dans le navigateur → l'assistant WordPress
   s'ouvre (langue : Français, titre : *TV Baaboul Houda*, identifiants admin).
6. **Permalinks** : *Réglages > Permalinks* > choisir **« %postname% »** (les
   réécritures du thème — `/ar/`, `/coran/{n}/`, `/manifest.json`, `sitemap.xml`
   — en dépendent).
7. **Activer** le plugin *Baaboul Houda Core* (il se-active aussi tout seul à la
   première visite de l'admin). Il **crée automatiquement toutes les pages
   sections** (En direct, Radio, Programmes, Replays, Coran, Prières,
   Événements, Animateurs, Contact, À propos, Actualités) et définit « En
   direct » comme page d'accueil.
8. **HTTPS** : *Sécurité > SSL/TLS Status* > *Run AutoSSL* (ou certificat
   Let's Encrypt) puis forcer le HTTPS (l'option « Force HTTPS » de cPanel).
9. **Enregistrer** les réglages dans *TV Baaboul Houda > Réglages du site*
   (voir `docs/content-guide.md`).

**Temps total : ~15 minutes.**

## Méthode B — Git (si SSH activé)

```bash
cd public_html
git clone <url-du-repo> tmp && mv tmp/* . && rmdir tmp
bash scripts/vendor-core.sh      # télécharge le cœur WP épinglé
npm install && npm run build     # si Node dispo, sinon les assets sont déjà commités
```
Puis étapes 2, 4, 5, 6, 7, 8 ci-dessus.

## Vérification post-déploiement

| URL | Attendu |
|---|---|
| `/` | Accueil avec le player (EN DIRECT ou carte hors antenne) |
| `/ar/` | Même page en arabe, RTL |
| `/direct/` `/radio/` `/programmes/` `/emissions/` `/coran/` `/prieres/` `/actualites/` `/evenements/` `/animateurs/` `/contact/` `/apropos/` | Pages sections |
| `/coran/1/` … `/coran/114/` | Lecteur du Coran |
| `/manifest.json` | Manifest PWA |
| `/sitemap.xml` | Sitemap (FR + AR) |
| `/wp-admin` | Admin — le menu **TV Baaboul Houda** est présent |

## Mise à jour

- **Contenu** (programmes, directs, grille…) : tout se fait dans l'admin — pas
  de code, pas de redéploiement.
- **Code/theme** : relancer `npm run release`, re-téléverser le zip dans
  `public_html` (la base de données et `wp-config.php` ne sont pas dans le zip →
  votre configuration et vos contenus sont préservés).

## Sauvegarde

- Fichiers : zip de `public_html` (ou Git).
- Base : *cPanel > Bases de données MySQL > Backup* (phpMyAdmin > Exporter).
  Pensez à sauvegarder **au moins mensuellement** et avant chaque mise à jour.
