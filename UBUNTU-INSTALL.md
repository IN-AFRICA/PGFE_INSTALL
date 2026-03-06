# 🚀 Installation PGFE sur Ubuntu Server (Sans Docker)

**Version simplifiée ultra-rapide pour Ubuntu Server + MariaDB**

## ⚡ Installation en 2 minutes

### Une seule commande !

```bash
curl -fsSL https://raw.githubusercontent.com/IN-AFRICA/PGFE_INSTALL/main/install-ubuntu.sh | bash
```

Ou téléchargez et exécutez:

```bash
wget https://raw.githubusercontent.com/IN-AFRICA/PGFE_INSTALL/main/install-ubuntu.sh
chmod +x install-ubuntu.sh
./install-ubuntu.sh
```

## 🎯 Ce que fait le script

Le script **install-ubuntu.sh** fait **tout automatiquement** :

1. ✅ Vérifie que vous êtes sur Ubuntu/Debian
2. ✅ Installe tous les prérequis :
   - PHP 8.2 + extensions nécessaires
   - Composer
   - Node.js 20
   - pnpm
   - MariaDB (MySQL)
3. ✅ Télécharge le ZIP depuis GitHub
4. ✅ Dézippe le projet
5. ✅ Installe les dépendances backend (Composer)
6. ✅ Installe les dépendances frontend (pnpm)
7. ✅ Configure automatiquement `.env` avec identifiants de base de données
8. ✅ Crée la base de données MariaDB
9. ✅ Exécute les migrations
10. ✅ Crée des scripts `start.sh` et `stop.sh` pour démarrer/arrêter facilement

## 📋 Prérequis minimum

- **Ubuntu Server 20.04, 22.04 ou 24.04** (ou Debian)
- **Accès sudo** (le script demandera le mot de passe si nécessaire)
- **Connexion Internet**
- **5 GB d'espace disque**

C'est tout ! Le script installe le reste automatiquement.

## 🚀 Démarrage après installation

```bash
cd pgfe
./start.sh
```

### Accès à l'application

- **Frontend**: http://localhost:5173
- **Backend API**: http://localhost:8000
- **Documentation API**: http://localhost:8000/docs/api

### Connexion par défaut

- **Email**: `admin@example.com`
- **Mot de passe**: `password`

## 🛑 Arrêter l'application

```bash
cd pgfe
./stop.sh
```

## 📊 Comparaison avec PGFE_3.zip

| Aspect | PGFE_3.zip (Docker) | install-ubuntu.sh |
|--------|---------------------|-------------------|
| **Complexité** | 11 phases interactives | 1 commande automatique |
| **Docker requis** | ✅ Oui | ❌ Non |
| **Temps d'installation** | ~15-20 min | ~5-7 min |
| **Questions posées** | 11 (une par phase) | 2-3 seulement |
| **Taille téléchargement** | Images Docker + code | Code seulement |
| **Performance** | Overhead Docker | Performance native |
| **Simplicité** | 🟡 Moyenne | 🟢 Excellente |
| **Maintenance** | Difficile | Facile |

## 🔧 Informations d'installation

Après l'installation, toutes les informations importantes sont sauvegardées dans :

```bash
pgfe/INSTALLATION_INFO.txt
```

Ce fichier contient :
- Identifiants de base de données (générés automatiquement)
- Commandes utiles
- URLs d'accès
- Localisation des logs

## 📝 Commandes utiles

### Redémarrer l'application
```bash
cd pgfe
./stop.sh
./start.sh
```

### Voir les logs
```bash
tail -f /tmp/pgfe-backend.log
tail -f /tmp/pgfe-frontend.log
```

### Réinitialiser la base de données
```bash
cd pgfe/backend
php artisan migrate:fresh --seed
```

### Vider les caches
```bash
cd pgfe/backend
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Optimiser pour la production
```bash
cd pgfe/backend
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
```

## 🔐 Sécurité

### Identifiants de base de données

Le script génère automatiquement un mot de passe sécurisé pour la base de données. Retrouvez-le dans :
- `pgfe/backend/.env` (ligne `DB_PASSWORD=`)
- `pgfe/INSTALLATION_INFO.txt`

### Changer le mot de passe admin

Après la première connexion :

1. Connectez-vous avec `admin@example.com` / `password`
2. Allez dans votre profil
3. Changez le mot de passe

Ou via la ligne de commande :
```bash
cd pgfe/backend
php artisan tinker
```
```php
$admin = User::where('email', 'admin@example.com')->first();
$admin->password = Hash::make('nouveau-mot-de-passe-securise');
$admin->save();
```

## 🌐 Accès depuis le réseau

Par défaut, l'application est accessible uniquement depuis `localhost`. Pour y accéder depuis le réseau :

### Option 1 : Éditer les scripts

Modifiez `pgfe/start.sh` :
```bash
# Backend - remplacez 127.0.0.1 par 0.0.0.0
php artisan serve --host=0.0.0.0 --port=8000

# Frontend - ajoutez --host
pnpm dev --host
```

### Option 2 : Utiliser Nginx (production)

Configurez Nginx pour pointer vers `pgfe/backend/public` et servir le frontend depuis `pgfe/frontend/dist` après build.

## 🔄 Mise à jour

Pour mettre à jour vers une nouvelle version :

```bash
# Sauvegarder la config
cp pgfe/backend/.env pgfe/backend/.env.backup
cp pgfe/INSTALLATION_INFO.txt pgfe/INSTALLATION_INFO.txt.backup

# Supprimer l'ancien
rm -rf pgfe

# Réinstaller
./install-ubuntu.sh

# Restaurer la config
cp pgfe/backend/.env.backup pgfe/backend/.env

# Migrer
cd pgfe/backend
php artisan migrate --force
```

## 🐛 Dépannage

### Erreur: "Port 8000 already in use"

Un autre service utilise le port 8000. Options :

1. Arrêter le service conflictuel
2. Changer le port dans `start.sh` :
   ```bash
   php artisan serve --port=8080
   ```

### Erreur: "Connection refused" (base de données)

Vérifiez que MariaDB fonctionne :
```bash
sudo systemctl status mariadb
sudo systemctl start mariadb
```

Testez la connexion :
```bash
mysql -u pgfe_user -p -h 127.0.0.1
# Entrez le mot de passe depuis INSTALLATION_INFO.txt
```

### Erreur: "Permission denied" sur storage/

```bash
cd pgfe/backend
chmod -R 775 storage bootstrap/cache
```

### Frontend ne démarre pas

```bash
cd pgfe/frontend
rm -rf node_modules
pnpm install
pnpm dev
```

### MariaDB vs MySQL

MariaDB est un fork de MySQL et **100% compatible**. Le script utilise MariaDB par défaut sur Ubuntu car :
- Plus léger
- Meilleures performances
- Entièrement open source
- Installation plus simple

Vous pouvez aussi utiliser MySQL classique, ça fonctionnera exactement pareil !

## 📊 Ressources système

### Développement

- **RAM utilisée** : ~1.5 GB
- **CPU** : Faible (~10-20%)
- **Disque** : ~2 GB (avec dépendances)

### Production (avec Nginx)

- **RAM utilisée** : ~500 MB - 1 GB
- **CPU** : Très faible (~5%)
- **Disque** : ~1.5 GB

## 🎯 Pourquoi cette approche est meilleure que Docker ?

### Avantages sans Docker:

1. **Plus simple** : Une commande, tout est installé
2. **Plus rapide** : Pas d'overhead Docker
3. **Moins de ressources** : Pas de conteneurs qui tournent
4. **Débogage facile** : Logs directement accessibles
5. **Performances natives** : Pas de virtualisation
6. **Idéal pour VPS/VM** : Utilisation optimale des ressources

### Quand utiliser Docker?

Docker reste utile pour :
- Environnements complexes multi-services
- Déploiements sur plusieurs environnements identiques
- Isolation stricte entre applications
- Orchestration Kubernetes

Pour un serveur dédié à PGFE → **Installation native = mieux !**

## 📞 Support

### Documentation
- [README.md](README.md) - Vue d'ensemble et guide d'installation
- [DOCKER-README.md](DOCKER-README.md) - Alternative avec Docker

### Aide en ligne
- **GitHub Issues** : https://github.com/IN-AFRICA/PGFE_INSTALL/issues
- **Documentation API** : http://localhost:8000/docs (après démarrage)

## ✅ Checklist post-installation

Après l'installation, vérifiez :

- [ ] Le frontend est accessible : http://localhost:5173
- [ ] Le backend répond : http://localhost:8000
- [ ] Vous pouvez vous connecter avec admin@example.com / password
- [ ] Les seeders ont peuplé la base de données
- [ ] Vous avez sauvegardé `INSTALLATION_INFO.txt`
- [ ] Vous avez changé le mot de passe admin
- [ ] MariaDB démarre automatiquement : `sudo systemctl enable mariadb`

## 🎉 Prochaines étapes

1. Explorez l'interface
2. Créez vos premières données (écoles, classes, etc.)
3. Testez les fonctionnalités (présences, paiements, exports)
4. Configurez pour la production (Nginx, SSL, backups)
5. Formez les utilisateurs

---

**Note** : Ce script est optimisé pour Ubuntu Server. Pour Docker, consultez [DOCKER-README.md](DOCKER-README.md).
