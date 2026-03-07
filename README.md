# PGFE - Plateforme de Gestion Financière et Éducative

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-red)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.5-green)](https://vuejs.org)
[![MariaDB](https://img.shields.io/badge/MariaDB-10.5%2B-blue)](https://mariadb.org)

Application web full-stack pour la gestion multi-établissements scolaires incluant la gestion financière, éducative, des présences et des cotations.

---

## 🚀 Installation rapide

Le script télécharge automatiquement la dernière release, installe les prérequis, configure la base de données et génère les scripts de démarrage.

### Linux (Debian/Ubuntu)

```bash
curl -fsSL https://raw.githubusercontent.com/IN-AFRICA/PGFE_INSTALL/main/install.sh -o install.sh
chmod +x install.sh
./install.sh
```

### Windows

```powershell
Invoke-WebRequest -Uri "https://raw.githubusercontent.com/IN-AFRICA/PGFE_INSTALL/main/install.cmd" -OutFile "install.cmd"
.\install.cmd
```

> **Note :** Le script Windows nécessite les droits administrateur et utilise `winget` pour installer les dépendances.

### Ce que fait le script

| Étape | Description |
|-------|-------------|
| 1 | Installe les prérequis (PHP 8.2, Composer, Node.js 20, pnpm, MariaDB) |
| 2 | Télécharge et extrait la dernière release depuis GitHub |
| 3 | Installe les dépendances backend (Composer) et configure le `.env` |
| 4 | Installe les dépendances frontend (pnpm) |
| 5 | Crée la base de données et exécute les migrations |
| 6 | Génère les scripts `start` et `stop` |

### Après l'installation

```bash
cd pgfe
./start.sh        # Linux
# ou
start.cmd          # Windows
```

| Service  | URL                       |
|----------|---------------------------|
| Frontend | http://localhost:5173      |
| Backend  | http://localhost:8000      |

Les identifiants de la base de données sont sauvegardés dans `pgfe/INSTALLATION_INFO.txt`.

---

## 🎯 Fonctionnalités

### Gestion Éducative
- 📚 Gestion des établissements multi-sites
- 🏫 Niveaux académiques, cycles et filières
- 👨‍🎓 Classes et inscriptions d'étudiants
- ✅ Gestion des présences
- 📝 Fiches de cotation (notes)

### Gestion Financière
- 💰 Plan comptable complet
- 📊 Journaux comptables
- 💵 Taux de change
- 💳 Frais et paiements
- 📄 Motifs de paiement

### Gestion Administrative
- 👥 Utilisateurs et rôles (RBAC)
- 🔐 Permissions granulaires
- 📋 Logs d'activité (audit trail)
- 📤 Exports Excel massifs (15+ types)
- 📑 Génération de PDF

### API & Documentation
- 🔌 API REST complète
- 📖 Documentation auto-générée (OpenAPI 3.0)
- 🔒 Authentification par tokens (Sanctum)
- 🔍 Filtrage et tri avancés

---

## 🏗️ Architecture

### Backend
- **Framework** : Laravel 12
- **PHP** : 8.2+
- **Base de données** : MariaDB 10.5+ / MySQL 8.0+
- **API** : RESTful avec Sanctum

### Frontend
- **Framework** : Vue.js 3.5
- **Language** : TypeScript
- **Build** : Vite
- **UI** : Tailwind CSS 4 + Reka UI
- **État** : Pinia

### Sécurité
- Authentication : Laravel Sanctum
- Authorization : Spatie Permission (RBAC)
- Audit : Spatie Activity Log
- CSRF, XSS, SQL Injection protections

---

## 💻 Développement

### Backend (Laravel)

```bash
cd backend

# Installer les dépendances
composer install

# Configuration
cp .env.example .env
php artisan key:generate

# Base de données
php artisan migrate

# Démarrer le serveur
php artisan serve
# → http://localhost:8000
```

### Frontend (Vue.js)

```bash
cd frontend

# Installer les dépendances
pnpm install

# Développement
pnpm dev --host --open
# → http://localhost:5173

# Build de production
pnpm build
```

---

## 📊 Stack technique détaillée

### Backend
| Package | Version | Usage |
|---------|---------|-------|
| Laravel | 12.x | Framework PHP |
| Sanctum | 4.x | API Authentication |
| Spatie Permission | 6.x | Gestion des rôles |
| Spatie Media Library | 11.x | Gestion des fichiers |
| Maatwebsite Excel | 4.x | Exports Excel |
| DomPDF | 2.x | Génération PDF |

### Frontend
| Package | Version | Usage |
|---------|---------|-------|
| Vue.js | 3.5.x | Framework JS |
| TypeScript | Latest | Typage statique |
| Tailwind CSS | 4.x | Framework CSS |
| Pinia | 3.x | State management |
| Vee-validate + Zod | Latest | Validation de formulaires |

---

## 🧪 Tests

### Backend (Pest)
```bash
cd backend
php artisan test
```

### Frontend (Vitest)
```bash
cd frontend
pnpm test:unit
```

---

## 🚀 Déploiement en production

### Optimisations Laravel

```bash
cd backend
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
```

---

## 📞 Support

- **Issues** : [GitHub Issues](https://github.com/IN-AFRICA/PGFE_INSTALL/issues)
- **Discussions** : [GitHub Discussions](https://github.com/IN-AFRICA/PGFE_INSTALL/discussions)
- **API Documentation** : http://localhost:8000/docs (après installation)

---

## 🤝 Contribution

Les contributions sont les bienvenues ! Veuillez :

1. Fork le projet
2. Créer une branche (`git checkout -b feature/AmazingFeature`)
3. Commiter vos changements (`git commit -m 'Add some AmazingFeature'`)
4. Pusher vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

---

## 📝 Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

---

## 📈 Roadmap

- [ ] Module de messagerie interne
- [ ] Génération automatique d'emplois du temps
- [ ] Application mobile (React Native)
- [ ] Notifications push
- [ ] Intégration paiement mobile
- [ ] Rapports avancés avec graphiques
- [ ] Mode hors-ligne (PWA)

---

**Développé avec ❤️ pour l'éducation en Afrique**
