# 🐳 PGFE Docker - Guide Complet

## Vue d'ensemble

Cette implémentation Docker fournit une stack complète et fiable pour PGFE :
- **Frontend** : Vue.js sur Nginx (port 80)
- **Backend API** : Laravel sur PHP-FPM proxifié via Nginx (port 8400)
- **Database** : MySQL 8.0 (port 3306)
- **Orchestration** : Docker Compose v3.8

---

## 📋 Architecture

```
┌──────────────────────────────────────────────────────────────┐
│                        Utilisateur                           │
└───────────────────┬──────────────────────┬──────────────────┘
                    │                      │
        Port 80     │        Port 8400     │  Port 3306
                    ↓                      ↓        ↓
          ┌─────────────────┐  ┌──────────────────┐  ┌──────────┐
          │ Frontend Nginx  │  │ API Nginx        │  │  MySQL   │
          │ (SPA/Port 80)   │  │ (Port 8400)      │  │ Database │
          └────────┬────────┘  └────────┬─────────┘  └──────────┘
                   │                    │
                   │                    │ fastcgi_pass
                   │                    ↓
                   │           ┌─────────────────┐
                   └──────────→│ Backend PHP-FPM │
                               └────────┬────────┘
                                        │
                                        ↓
                               ┌─────────────────┐
                               │ Laravel App     │
                               └────────┬────────┘
                                        │
                                        ↓
                               ┌─────────────────┐
                               │ MySQL (Docker)  │
                               └─────────────────┘
```

---

## 🚀 Démarrage Rapide

### 1. Vérifier les prérequis
```bash
docker --version      # Docker 20.10+
docker compose version # Docker Compose 2.0+
```

### 2. Configuration initiale
```bash
# Copier .env.example → .env (s'il n'existe pas déjà)
cp .env.example .env

# L'APP_KEY sera générée automatiquement par Docker
```

### 3. Démarrer les services

**Mode Production :**
```bash
# Avec docker compose directement
docker compose up -d

# Ou avec le script
./docker-launch.sh prod up

# Ou avec make
make up
```

**Mode Développement :**
```bash
# Avec docker compose et override
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d

# Ou avec le script
./docker-launch.sh dev up

# Ou avec make
make up MODE=dev
```

### 4. Initialisation de la base de données
Les migrations et seeders sont exécutés **automatiquement** au démarrage du conteneur backend grace à `docker-entrypoint.sh`.

Pour vérifier l'état :
```bash
docker compose logs backend  # Voir les logs du backend
docker compose ps            # Vérifier les healthchecks
```

### 5. Accéder aux services
- **Frontend** : http://localhost (port 80)
- **Backend API** : http://localhost:8400/api (port 8400)
- **Health Check API** : http://localhost:8400/health
- **Database** : `localhost:3306` (MySQL)

---

## 💻 Commandes Courantes

### Avec Docker Compose

```bash
# Démarrer (production)
docker compose up -d

# Démarrer (développement)
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d

# Arrêter
docker compose down

# Redémarrer
docker compose restart

# Logs
docker compose logs -f [service]  # backend, frontend, nginx-api, database

# État
docker compose ps

# Accès bash
docker compose exec backend bash
docker compose exec database bash
docker compose exec database mysql -u pgfe_user -p

# Migrations supplémentaires
docker compose exec backend php artisan migrate
docker compose exec backend php artisan db:seed
```

### Avec le script (Linux/Mac)

```bash
chmod +x docker-launch.sh

# Production
./docker-launch.sh prod up
./docker-launch.sh prod logs-f
./docker-launch.sh prod down

# Développement
./docker-launch.sh dev up
./docker-launch.sh dev logs-f
./docker-launch.sh dev exec-api      # bash dans backend
./docker-launch.sh dev exec-db       # mysql cli
```

### Avec Make (Linux/Mac)

```bash
make help           # Afficher l'aide
make setup          # Configuration initiale
make up             # Démarrage (prod)
make up MODE=dev    # Démarrage (dev)
make down           # Arrêt
make logs-f         # Logs continus
make bash-api       # Accès bash backend
make bash-db        # Accès MySQL
make migrate        # Migrations Laravel
make seed           # Seeders
make clean          # Nettoyage complet
```

---

## 📁 Structure des Fichiers

```
.
├── docker-compose.yml              # Config production (base)
├── docker-compose.dev.yml          # Override développement
├── .env                            # Variables d'environnement
├── .env.example                    # Template .env
├── docker-launch.sh                # Script bash pour lancer
├── Makefile                        # Commandes make
│
├── backend/
│   ├── Dockerfile                  # Build PHP-FPM
│   ├── Dockerfile.nginx-api        # Build nginx API gateway
│   ├── nginx-api.conf              # Config nginx port 8400
│   ├── docker-entrypoint.sh        # Entrypoint (migrations, seeders)
│   ├── .dockerignore               # Fichiers à exclure du build
│   └── ...
│
├── frontend/
│   ├── Dockerfile                  # Build Nginx SPA
│   ├── nginx.conf                  # Config nginx port 80
│   ├── .dockerignore               # Fichiers à exclure du build
│   └── ...
│
└── DOCKER-GUIDE.md                 # Ce fichier
```

---

## 🔧 Variables d'Environnement (.env)

```bash
# Application
APP_NAME=PGFE
APP_ENV=production          # local|production
APP_DEBUG=false             # true|false (toujours false en prod)
APP_KEY=                    # Généré automatiquement
APP_TIMEZONE=Africa/Lubumbashi
APP_URL=http://localhost

# Database (interne Docker)
DB_HOST=database            # Nom du service Docker
DB_PORT=3306
DB_DATABASE=pgfe_db
DB_USERNAME=pgfe_user
DB_PASSWORD=pgfe_secret
DB_ROOT_PASSWORD=pgfe_root_secret

# Caching & Sessions
CACHE_STORE=file            # Stockage en fichiers
SESSION_DRIVER=file

# Autres
LOG_LEVEL=error             # debug|info|warning|error
```

**Important** : Pour le développement, vous pouvez modifier .env après le démarrage et redémarrer :
```bash
# Modifier .env
nano .env

# Redémarrer avec les nouvelles variables
docker compose restart backend
```

---

## 🏥 Healthchecks

Tous les services disposent de healthchecks automatiques :

```bash
# Vérifier l'état
docker compose ps

# STATUS devrait afficher:
# "healthy" = OK ✓
# "unhealthy" = Erreur ✗
# "starting" = En démarrage...
```

### Services et leurs healthchecks :

| Service | Healthcheck | Interval |
|---------|------------|----------|
| **database** | `mysqladmin ping` | 10s |
| **backend** | `php-fpm -t` | 30s |
| **nginx-api** | `GET /health` → 200 | 30s |
| **frontend** | `GET /health` → 200 | 30s |

---

## 🔐 Configuration de Sécurité

### Headers de sécurité
- `X-Frame-Options: SAMEORIGIN`
- `X-Content-Type-Options: nosniff`
- `X-XSS-Protection: 1; mode=block`
- `Strict-Transport-Security` (HSTS)

### CORS
L'API supporte CORS pour les requests depuis le frontend et autres domaines :
- Méthodes : `GET, POST, PUT, PATCH, DELETE, OPTIONS`
- Headers customisés autorisés
- Credentials supportés

### Fichiers sensibles
- `.env` : Bloqué par nginx (403)
- `.ht*` : Bloqué par nginx (403)
- `vendor/`, `node_modules/` : Pas inclus dans les images Docker

---

## 🐛 Dépannage

### Les conteneurs ne démarrent pas

```bash
# Vérifier les logs
docker compose logs

# Vérifier les healthchecks
docker compose ps

# Voir plus de détails sur un service
docker compose logs backend   # ou frontend, nginx-api, database
```

### "Connection refused" sur 8400

```bash
# Vérifier que nginx-api est en cours d'exécution
docker compose ps nginx-api

# Vérifier que backend php-fpm est healthy
docker compose ps backend

# Vérifier le healthcheck de nginx-api
docker compose logs nginx-api
```

### "Can't connect to database"

```bash
# Vérifier que database est healthy
docker compose ps database

# Vérifier les logs
docker compose logs database

# Vérifier les credentials dans .env
cat .env | grep DB_
```

### Erreur "Port already in use"

```bash
# Voir quel processus utilise le port
# Linux/Mac:
lsof -i :80
lsof -i :8400
lsof -i :3306

# Windows (PowerShell):
netstat -ano | findstr :80
netstat -ano | findstr :8400

# Solution: Changer les ports dans docker-compose.yml ou .env
```

### Réinitialiser complètement

```bash
# ⚠️ ATTENTION : Cela supprime TOUTES les données

docker compose down -v           # Supprimer conteneurs + volumes
docker compose build --no-cache  # Reconstruire les images
docker compose up -d             # Redémarrer
```

---

## 📊 Mode Développement vs Production

### Development (`docker compose.dev.yml`)

```bash
make up MODE=dev

# Caractéristiques:
✓ Volumes mountés (code source)
✓ Hot reload possible
✓ APP_DEBUG=true
✓ Logs détaillés
✓ Base de données persistante
✗ Performance réduite
✗ Images plus grandes
```

**Fichiers modifiés détectés automatiquement** :
```bash
backend/    → Rechargement auto en FPM
frontend/src/ → Rechargement auto en Vite
```

### Production (`docker-compose.yml`)

```bash
make up

# Caractéristiques:
✓ Images optimisées
✓ Code immuable
✓ Performance maximale
✓ APP_DEBUG=false
✓ Logs minimaux
✗ Pas de hot reload
✗ Modifications = rebuild
```

---

## 🗄️ Base de Données

### Accès MySQL

```bash
# Avec docker compose
docker compose exec database mysql -u pgfe_user -p

# Ou spécifier la base
docker compose exec database mysql -u pgfe_user -p pgfe_db

# Password: pgfe_secret (ou celle dans .env)
```

### Commandes Laravel courantes

```bash
docker compose exec backend php artisan migrate          # Migrer
docker compose exec backend php artisan db:seed          # Seeder
docker compose exec backend php artisan migrate:fresh    # Réinitialiser
docker compose exec backend php artisan tinker           # REPL interactif
```

### Seeders disponibles

Le projet inclut plusieurs seeders :
- `RolesAndPermissionsSeeder` - Rôles et permissions d'accès
- `SuperAdminSeeder` - Compte super admin
- `UserRoleDemoSeeder` - Utilisateurs démo
- Et environ 40+ autres seeders...

Tous sont exécutés automatiquement au démarrage via `docker-entrypoint.sh`.

Pour sauter le seeding :
```bash
export SKIP_SEEDING=1
docker compose up -d
```

---

## 📝 Logs et Monitoring

### Voir les logs

```bash
# Derniers 50 lignes
docker compose logs

# Logs continus
docker compose logs -f

# Spécifier un service
docker compose logs -f backend
docker compose logs -f frontend
docker compose logs -f nginx-api
docker compose logs -f database

# Depuis les 5 dernières minutes
docker compose logs --since 5m
```

### Logs dans les conteneurs

```bash
# Backend Laravel
docker compose exec backend tail -f storage/logs/laravel.log

# Nginx API
docker compose exec nginx-api tail -f /var/log/nginx/api_access.log
docker compose exec nginx-api tail -f /var/log/nginx/api_error.log

# Frontend Nginx
docker compose exec frontend tail -f /var/log/nginx/access.log
```

---

## 🎯 Cas d'Usage Courants

### Modifier le code backend

```bash
# 1. Mode développement
make up MODE=dev

# 2. Éditer les fichiers backend/ normalement
# (Ils sont mountés dans le conteneur)

# 3. Les changements sont visibles immédiatement
# (Grâce aux volumes + FPM reload)

# 4. Vérifier les logs
make logs-f
```

### Ajouter une migration

```bash
# 1. Créer la migration (depuis la machine hôte)
cd backend
php artisan make:migration create_new_table

# 2. Éditer la migration

# 3. Lancer depuis Docker
docker compose exec backend php artisan migrate
```

### Utiliser une base de données externe

```bash
# Modifier .env
DB_HOST=votre-serveur.com
DB_PORT=3306
DB_USERNAME=user
DB_PASSWORD=pass

# Redémarrer
docker compose restart backend

# Vérifier la connexion
docker compose logs backend
```

### Activer HTTPS en production

```bash
# Option 1: Load balancer externe (Recommended)
# Placer nginx/Apache devant Docker

# Option 2: Certificat auto-signé (dev)
# Ajouter à nginx-api.conf:
# listen 443 ssl;
# ssl_certificate /etc/nginx/certs/cert.pem;
# ssl_certificate_key /etc/nginx/certs/key.pem;
```

---

## 📦 Backup & Restore

### Backup de la base de données

```bash
# Dump MySQL
docker compose exec database mysqldump -u pgfe_user -p pgfe_db > backup.sql

# Avec compression
docker compose exec database mysqldump -u pgfe_user -p pgfe_db | gzip > backup.sql.gz
```

### Restaurer une base de données

```bash
# Restaurer
docker compose exec -T database mysql -u pgfe_user -p pgfe_db < backup.sql

# Ou depuis la machine hôte
cat backup.sql | docker compose exec -T database mysql -u pgfe_user -p pgfe_db
```

### Backup des fichiers uploadés

```bash
# Les fichiers sont dans le volume backend-storage
docker cp pgfe-backend:/var/www/html/storage ./storage-backup
```

---

## 🚀 Déploiement en Production

### Checklist

```bash
# 1. Définir les variables sécurisées
APP_ENV=production
APP_DEBUG=false
APP_KEY=[GÉNÉRER UNE CLÉ SÉCURISÉE]
DB_PASSWORD=[CHANGER LE MOT DE PASSE]
DB_ROOT_PASSWORD=[CHANGER LE MOT DE PASSE]

# 2. Mettre à jour APP_URL
APP_URL=https://votre-domaine.com

# 3. Configuration du cache
CACHE_STORE=file  # Ou utiliser Redis si le volume est instable

# 4. Configuration du mail
MAIL_MAILER=smtp  # Ou MAILGUN, SENDGRID, etc.
MAIL_HOST=votre-smtp
MAIL_PORT=587
```

### Architecture recommandée

```
┌─────────────────┐
│ Reverse Proxy   │ (nginx, Apache, Cloudflare)
│ (HTTPS, CORS)   │
└────────┬────────┘
         │ HTTP
         ↓
┌─────────────────────────────────────┐
│     Docker Compose (PGFE)           │
│ ┌─────────┐ ┌──────────┌─────────┐ │
│ │Frontend │ │nginx-api │ Backend │ │
│ │(port 80)│ │(port80)  │(FPM)    │ │
│ └─────────┘ └──────────┴────┬────┘ │
│                             │      │
│                        ┌────▼───┐  │
│                        │ MySQL   │  │
│                        └────────┘  │
└─────────────────────────────────────┘
```

---

## Troubleshooting

### Erreur: "npm ERR! errno ETIMEDOUT"

**Cause** : Timeout réseau lors du build frontend

**Solution** :
```dockerfile
# Déjà configuré dans le Dockerfile frontend:
RUN npm config set fetch-retry-maxtimeout 1200000
```

Reconstruire :
```bash
docker compose build --no-cache frontend
```

### Erreur: "php artisan migrate" hangs

**Cause** : Base de données pas prête

**Solution** :
```bash
# Attendre les healthchecks
docker compose ps  # Attendre que database soit "healthy"

# Ou relancer manuellement après quelques secondes
sleep 30
docker compose exec backend php artisan migrate
```

### Memory/Performance issues

```bash
# Augmenter la limite mémoire PHP
# Dans docker-compose.yml, ajouter:
environment:
  - PHP_MEMORY_LIMIT=1024M

# Ou limiter les ressources Docker
# (Dans docker-compose.yml):
deploy:
  resources:
    limits:
      cpus: '1'
      memory: 2G
```

---

## 🔗 Ressources Utiles

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Spec](https://github.com/compose-spec/compose-spec)
- [Laravel Deployment](https://laravel.com/docs/deployment)
- [Vue.js Production](https://vuejs.org/guide/best-practices/production.html)
- [Nginx Configuration](https://nginx.org/en/docs/)

---

## 📞 Support

Pour toute question ou problème :
1. Vérifier les logs : `docker compose logs -f`
2. Vérifier l'état : `docker compose ps`
3. Vérifier la configuration : `cat .env`
4. Consulter cette documentation

---

**Dernière mise à jour** : 2026-04-05  
**Version** : 1.0.0
