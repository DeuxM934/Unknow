# UNKNOW® Streetwear — prototype PHP

Boutique PHP avec design streetwear, catalogue, panier de session et espace admin pour gérer les produits.

## Structure
- `index.php` : boutique, catalogue, panier, page de checkout d'aperçu.
- `style.css` : style streetwear responsive.
- `lib.php` : lecture/écriture du catalogue JSON.
- `data/products.json` : produits de démonstration.
- `admin/login.php` : connexion admin.
- `admin/dashboard.php` : ajouter, modifier et supprimer des produits.
- `admin/logout.php` : déconnexion.
- `api/health.php` : endpoint de test.
- `vercel.json` : configuration d'exemple pour un runtime PHP communautaire.

## Lancer en local
Installe PHP 8.1 ou plus récent, puis dans ce dossier :
```bash
php -S localhost:8000
```
Ouvre `http://localhost:8000`.

## Accès admin
Pour créer le compte, génère un hash de mot de passe :
```bash
php -r "echo password_hash('TON_MOT_DE_PASSE_LONG', PASSWORD_DEFAULT), PHP_EOL;"
```
Crée `data/admin.json` avec ce contenu en remplaçant les valeurs :
```json
{
  "username": "admin",
  "password_hash": "COLLE_ICI_LE_HASH_GENERE"
}
```
Ne publie jamais ce fichier dans un dépôt public. Ajoute `data/admin.json` au `.gitignore` avant de pousser le projet. Le fichier est ignoré dans l'archive par défaut, afin de ne pas inclure de mot de passe.

## Vercel : points importants
Vercel n'est pas un hébergement PHP traditionnel. Le fichier `vercel.json` utilise un runtime PHP communautaire à titre de point de départ ; vérifie sa compatibilité et son état de maintenance avant de déployer, car ce n'est pas une garantie de support officiel. La configuration de routage d'une app PHP peut nécessiter des ajustements selon le runtime choisi.

**Important : Vercel utilise un système de fichiers généralement éphémère et non adapté à l'écriture persistante de `data/products.json`.** Donc l'administration qui modifie le catalogue ne sera pas persistante après déploiement. Pour une vraie version Vercel, remplace le JSON par une base de données persistante (par exemple PostgreSQL/Supabase/Neon) et stocke les photos dans un stockage dédié (par exemple Supabase Storage ou Cloudinary). Les sessions PHP et leur persistance doivent aussi être vérifiées ou remplacées par un mécanisme adapté au déploiement serverless.

## Paiement carte + PayPal
Les boutons de paiement sont volontairement désactivés dans cette version. Aucun numéro de carte ne doit être collecté par ce site. Pour activer les paiements :
1. Crée un compte marchand chez Stripe pour les paiements carte et un compte PayPal Business.
2. Implémente la création de session de paiement côté serveur, avec clés API dans les variables d'environnement de l'hébergement (jamais dans le code source).
3. Vérifie les webhooks de paiement côté serveur et enregistre chaque commande dans une base de données persistante.
4. Teste les paiements en mode test avant d'ouvrir la boutique.
Ne marque jamais une commande comme payée sur la seule base d'un retour navigateur.

## Avant les ventes réelles
- Ajouter mentions légales, CGV, politique de confidentialité, informations de livraison/retour et contact.
- Configurer TVA/facturation selon ta situation.
- Protéger l'admin (mot de passe fort, HTTPS, limitation des tentatives, cookies de session sécurisés, CSRF).
- Ajouter une base de données persistante, gestion des commandes et des stocks.
- Vérifier la conformité et tester tout le parcours de commande.

Ce projet est une base de développement, pas encore une boutique prête à accepter des paiements réels.
