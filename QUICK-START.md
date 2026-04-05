# 🚀 Quick Start - PGFE Docker

Commencez à utiliser PGFE avec Docker en quelques commandes !

## 🔧 Configuration Initiale (1 minute)

```bash
# 1. Copier la configuration
cp .env.example .env

# 2. Démarrer tous les services
# Windows (PowerShell):
.\docker-launch.bat prod up

# Linux/Mac:
./docker-launch.sh prod up

# Ou juste:
docker compose up -d
```

**C'est tout !** ✅ Les services vont démarrer et initialiser la base de données automatiquement.

---

## 🌍 Accéder aux Applications

Une fois que tous les services sont **"healthy"** (vérifier avec `docker compose ps`):

| Service | URL | Port |
|---------|-----|------|
| **Frontend Vue.js** | http://localhost | 80 |
| **Backend API** | http://localhost:8400/api | 8400 |
| **API Health** | http://localhost:8400/health | 8400 |
| **Database MySQL** | localhost:3306 | 3306 |

---

## 📋 Commandes Essentielles

### Démarrage

```bash
# Production
docker compose up -d

# Développement (avec volumes mounted)
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d

# Avec logs visibles
docker compose up
```

### Arrêt & Redémarrage

```bash
# Arrêter
docker compose down

# Redémarrer
docker compose restart
```

### Logs

```bash
# Tous les logs (dernières 50 lignes)
docker compose logs

# Logs continus (Ctrl+C pour quitter)
docker compose logs -f

# Un service spécifique
docker compose logs -f backend
docker compose logs -f frontend
docker compose logs -f nginx-api
docker compose logs -f database
```

### État

```bash
# Vérifier les conteneurs et healthchecks
docker compose ps

# Si vous avez Windows + PowerShell:
.\docker-health-check.ps1
```

---

## 🗄️ Base de Données

### Accès MySQL

```bash
# CLI MySQL
docker compose exec database mysql -u pgfe_user -p pgfe_db
# Password: pgfe_secret (ou celle dans .env)

# Ou directement depuis votre client MySQL:
# Host: localhost
# Port: 3306
# User: pgfe_user
# Password: pgfe_secret
```

### Migrations & Seeders

```bash
# Les migrations et seeders sont lancés AUTOMATIQUEMENT au démarrage

# Pour relancer manuellement:
docker compose exec backend php artisan migrate
docker compose exec backend php artisan db:seed

# Réinitialiser complètement:
docker compose exec backend php artisan migrate:fresh --seed
```

---

## 🔄 Développement

### Mode Développement (hot reload)

```bash
# Démarrer en dev
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d

# Les fichiers source sont mounted:
# - backend/ → disponible dans le conteneur
# - frontend/src/ → disponible dans le conteneur

# Les changements sont visibles en direct (sans rebuild)
```

### Accès aux shells

```bash
# Bash dans le backend Laravel
docker compose exec backend bash

# MySQL cli
docker compose exec database mysql -u pgfe_user -p

# Artisan interactif (tinker)
docker compose exec backend php artisan tinker
```

---

## ⚙️ Configuration

Le fichier `.env` contrôle tout:

```bash
# Application
APP_ENV=production     # production ou local
APP_DEBUG=false        # true ou false
APP_KEY=               # Généré automatiquement

# Database
DB_HOST=database
DB_DATABASE=pgfe_db
DB_USERNAME=pgfe_user
DB_PASSWORD=pgfe_secret

# Otros
SESSION_DRIVER=file
CACHE_STORE=file
LOG_LEVEL=error
```

**Modifier .env** :
```bash
# Éditer le fichier
nano .env            # Linux/Mac
notepad .env         # Windows

# Redémarrer le service modifié
docker compose restart backend  # Si changement DB
docker compose restart frontend # Si changement frontend
```

---

## 🆘 Problèmes Courants

### Les services ne démarrent pas

```bash
# Vérifier les logs
docker compose logs

# Vérifier les healthchecks
docker compose ps
```

### "Connection refused" à l'API

```bash
# Vérifier que nginx-api est en cours d'exécution et healthy
docker compose ps nginx-api

# Attendre le healthcheck (peut prendre 30-60 secondes)
docker compose logs nginx-api
```

### Port déjà utilisé

```bash
# Vérifier quel service utilise le port (Windows PowerShell):
netstat -ano | findstr :80
netstat -ano | findstr :8400

# Vérifier les conteneurs:
docker ps     # Voir les conteneurs en cours

# Libérer le port:
# Option 1: Arrêter le service qui l'utilise
docker compose down

# Option 2: Changer le port dans docker-compose.yml
```

### Base de données lente ou inaccessible

```bash
# Vérifier l'état
docker compose ps database

# Voir les logs
docker compose logs database

# Redémarrer
docker compose restart database
```

---

## 🧹 Nettoyage

```bash
# Arrêter les services (données conservées)
docker compose down

# Supprimer les conteneurs
docker compose down

# Supprimer aussi les volumes (⚠️ ATTENTION: efface la DB)
docker compose down -v

# Reconstruire les images (si changement Dockerfile)
docker compose build --no-cache
docker compose up -d
```

---

## 💡 Astuces

### Voir les logs en temps réel pendant le démarrage

```bash
docker compose up        # Sans -d pour voir les logs
```

### Entrer dans la base de données pour des requêtes SQL

```bash
docker compose exec database mysql -u pgfe_user -p pgfe_db
# Puis taper des requêtes SQL
```

### Exécuter des commandes Artisan Laravel

```bash
docker compose exec backend php artisan [command]

# Exemples:
docker compose exec backend php artisan make:migration
docker compose exec backend php artisan make:model User
docker compose exec backend php artisan cache:clear
```

### Vérifier les permissons dans le conteneur

```bash
docker compose exec backend bash
ls -la storage/         # Vérifier les droits
chmod 775 storage/*     # Corriger si nécessaire
```

---

## 📚 Plus d'Infos

Pour une documentation complète :
→ Voir **DOCKER-GUIDE.md**

Pour les commandes avancées :
```bash
# Linux/Mac:
./docker-launch.sh help

# Windows:
.\docker-launch.bat help

# Ou:
make help
```

---

## ✅ Check-liste après démarrage

- [ ] Tous les conteneurs sont "healthy" (`docker compose ps`)
- [ ] Frontend accessible sur http://localhost
- [ ] API accessible sur http://localhost:8400/health
- [ ] Bases de données accessible sur localhost:3306
- [ ] Logs ne montrent pas d'erreur (`docker compose logs`)
- [ ] Premiers seeders ont chargé (vérifier la DB)

Bravo ! 🎉 Votre PGFE Docker est prêt !

---

**Besoin d'aide ?**
- Vérifier les logs: `docker compose logs -f`
- Voir l'état: `docker compose ps`
- Lire le guide complet: [DOCKER-GUIDE.md](DOCKER-GUIDE.md)
