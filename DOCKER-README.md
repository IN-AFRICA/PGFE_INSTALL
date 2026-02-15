# PGFE — Plateforme de Gestion Financière et Éducative
## 🐳 Version Docker

Cette archive contient l'application PGFE complète avec Docker Compose pour un déploiement simplifié.

## 📁 Structure

```
pgfe/
├── docker-compose.yml          # Orchestrateur principal
├── .env.example               # Configuration d'environnement
├── frontend/                  # Application Vue.js
│   ├── Dockerfile            # Build frontend (nginx)
│   ├── nginx.conf           # Configuration SPA routing
│   └── .dockerignore
└── backend/                   # Application Laravel
    ├── Dockerfile            # Build backend (PHP 8.2)
    └── .dockerignore
```

## 🚀 Démarrage rapide

### 1. Configuration
```bash
cp .env.example .env
# Modifiez les variables DB_* selon vos besoins
```

### 2. Lancement
```bash
docker compose up -d
```

### 3. Initialisation base de données
```bash
# Attendre que les conteneurs soient prêts (30s)
docker compose exec backend php artisan migrate --force
docker compose exec backend php artisan db:seed --force
```

### 4. Accès
- **Frontend** : http://localhost
- **Backend API** : http://localhost/api/
- **Database** : localhost:3306

## 🔧 Services

| Service | Image | Port | Description |
|---------|-------|------|-------------|
| **frontend** | nginx:alpine | 80 | Interface Vue.js avec SPA routing |
| **backend** | php:8.2-fpm | 8000 | API Laravel avec extensions complètes |
| **database** | mysql:8.0 | 3306 | Base de données MySQL |

## 📊 Healthchecks

Tous les services disposent de healthchecks automatiques :
- **Frontend** : `GET /health` → 200 "ok"
- **Backend** : `php artisan about`
- **Database** : `mysqladmin ping`

## 🛠️ Gestion

### Logs
```bash
docker compose logs -f [service]
```

### Arrêt
```bash
docker compose down
```

### Mise à jour
```bash
docker compose pull
docker compose up -d --build
```

### Reset complet
```bash
docker compose down -v  # ⚠️ Supprime les données
docker compose up -d --build
```

## 🔐 Comptes par défaut

Après `db:seed`, les comptes suivants sont disponibles :
- **Super Admin** : Défini par le `SuperAdminSeeder`
- **Utilisateurs demo** : Créés par le `UserRoleDemoSeeder`

## 📋 Prérequis système

- Docker 20.10+
- Docker Compose 2.0+
- 4 Go RAM minimum
- 10 Go espace disque

## 🐛 Dépannage

### Ports occupés
```bash
# Changer les ports dans docker-compose.yml
ports:
  - "8080:80"   # Frontend sur port 8080
  - "3307:3306" # MySQL sur port 3307
```

### Permissions Laravel
```bash
docker compose exec backend chmod -R 775 storage bootstrap/cache
```

### Cache Laravel
```bash
docker compose exec backend php artisan cache:clear
docker compose exec backend php artisan config:clear
```

---

**📧 Support** : IN-AFRICA Team  
**🔄 Version** : Compatible avec les scripts d'installation PGFE_2