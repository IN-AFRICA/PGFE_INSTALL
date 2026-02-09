# PGFE — Plateforme de Gestion Financière et Éducative

PGFE est une application Laravel destinée à la gestion des écoles (multi‑établissements), incluant la gestion des utilisateurs et rôles, des infrastructures, des paiements, ainsi que des outils d’export et de synchronisation.

Ce document explique comment installer, lancer, peupler la base de données et utiliser les commandes principales du projet.

## Sommaire
- Prérequis
- Installation rapide
- Configuration de l’environnement
- Base de données et seeders
- Commandes Artisan utiles
- Comptes de démonstration
- Démarrage de l’application
- Tests
- Dépannage rapide (FAQ)
- Sécurité et bonnes pratiques

## Prérequis
- PHP 8.2+ (recommandé)
- Composer 2.x
- Node.js 18+ et npm
- Serveur de base de données (MySQL/MariaDB ou équivalent supporté par Laravel)
- Extensions PHP usuelles pour Laravel (mbstring, tokenizer, json, pdo, openssl, ctype, xml, gd, etc.)

## Installation rapide
1) Cloner le dépôt puis installer les dépendances PHP et Node:
```
composer install
npm install
```

2) Copier la configuration d’exemple et générer la clé d’application:
```
cp .env.example .env
php artisan key:generate
```

3) Renseigner les variables de connexion à la base de données dans `.env` (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD, …).

4) Lier le stockage et construire les assets:
```
php artisan storage:link
npm run build   # ou: npm run dev
```

## Base de données et seeders
Appliquer les migrations et exécuter les seeders:
```
php artisan migrate --force
php artisan db:seed --force
```

Seeders notables:
- `Database\Seeders\SuperAdminSeeder` — crée un super‑admin idempotent.
- `Database\Seeders\UserRoleDemoSeeder` — crée des écoles, rôles et utilisateurs de démonstration de manière idempotente.

Les seeders ont été conçus pour être ré‑exécutables sans erreurs d’unicité (idempotence).

## Commandes Artisan utiles
- Export PDF des utilisateurs, rôles et statut des mots de passe (option 1 – aucun reset):
```
php artisan users:export-pdf
```
Le PDF est généré dans `storage/app/exports/` avec un nom daté comme `users_passwords_YYYYMMDD_HHMMSS.pdf`.

- Synchronisation avec un serveur distant (multi‑tenant):
```
php artisan app:sync-remote               # toutes les tables
php artisan app:sync-remote --table=nom   # une table précise
```

## Comptes de démonstration
Les comptes suivants sont créés par les seeders par défaut:
- superadmin@pgfe.com — rôle: `super-admin` — mot de passe: `SuperAdmin@2025`
- elvis1@gmail.com — rôle: `admin` — mot de passe: `codecode`
- admin1@gmail.com — rôle: `admin` — mot de passe: `codecode`
- admin-ecole@gmail.com — rôle: `admin-ecole` — mot de passe: `codecode`
- admin-ecole2@gmail.com — rôle: `admin-ecole` — mot de passe: `codecode`
- tiers@gmail.com — rôle: `tiers` — mot de passe: `codecode`
- tiers2@gmail.com — rôle: `tiers` — mot de passe: `codecode`
- enseignant1@gmail.com — rôle: `enseignant` — mot de passe: `codecode`
- comptable1@gmail.com — rôle: `comptable` — mot de passe: `codecode`
- stoker1@gmail.com — rôle: `stoker` — mot de passe: `codecode`
- rh1@gmail.com — rôle: `rh` — mot de passe: `codecode`
- inspecteur1@gmail.com — rôle: `inspecteur` — mot de passe: `codecode`
- disciplinaire1@gmail.com — rôle: `disciplinaire` — mot de passe: `codecode`

Note: pour les utilisateurs non créés par les seeders, le mot de passe n’est pas récupérable en clair (hash). Utilisez une réinitialisation si nécessaire.

## Démarrage de l’application
Lancer le serveur de développement:
```
php artisan serve
```
Par défaut sur http://127.0.0.1:8000

## Tests
Exécuter la suite de tests:
```
php artisan test
```

## Dépannage rapide (FAQ)
- Erreur de contrainte d’unicité en reseed: les seeders principaux (`SuperAdminSeeder`, `UserRoleDemoSeeder`) sont idempotents. Assurez-vous d’exécuter `php artisan migrate` avant `db:seed` et vérifiez que les emails n’ont pas été modifiés manuellement.
- PDF non généré: vérifier que le disque `local` est accessible et que le dossier `storage/app/exports` existe (la commande le crée automatiquement). Vérifier aussi l’extension DOMPDF installée via Composer.
- Assets non chargés: relancer `npm run build` (ou `npm run dev`) et vider le cache du navigateur.

## Sécurité et bonnes pratiques
- Changez le mot de passe du super‑admin en production immédiatement après le premier login.
- Ne partagez jamais de PDF contenant des mots de passe par défaut en dehors d’un canal sécurisé.
- Maintenez vos dépendances à jour: `composer update` et `npm update` selon la politique de l’équipe.
- Configurez correctement les variables sensibles dans `.env` et ne les commitez jamais.

---

Pour toute question ou contribution, ouvrez une issue interne ou contactez le mainteneur du projet.
