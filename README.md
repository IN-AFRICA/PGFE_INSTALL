# PGFE - Plateforme de Gestion Financière et Éducative

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-red)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.5-green)](https://vuejs.org)
[![MariaDB](https://img.shields.io/badge/MariaDB-10.5%2B-blue)](https://mariadb.org)

Application web full-stack pour la gestion multi-établissements scolaires incluant la gestion financière, éducative, des présences et des cotations.

---

## 🚀 Installation rapide (Recommandé)

### Ubuntu Server (Sans Docker) - **Le plus simple !**

```bash
curl -fsSL https://raw.githubusercontent.com/IN-AFRICA/PGFE_INSTALL/main/install-ubuntu.sh | bash
```

✅ **Avantages** : Installation complète en 5 minutes, tout automatique, optimisé pour les performances

📖 **Documentation complète** : [UBUNTU-INSTALL.md](UBUNTU-INSTALL.md)

---

## 📦 Installation avec Docker (Alternative)

Si vous préférez Docker pour l'isolation :

```bash
git clone https://github.com/IN-AFRICA/PGFE_INSTALL.git
cd PGFE_INSTALL
cp .env.example .env
docker compose up -d
docker compose exec backend php artisan migrate --seed
```

📖 **Documentation Docker** : [DOCKER-README.md](DOCKER-README.md)

---

## 📚 Documentation

| Document | Description |
|----------|-------------|
| **[README.md](README.md)** | Ce fichier - Vue d'ensemble et installation |
| **[UBUNTU-INSTALL.md](UBUNTU-INSTALL.md)** | 📖 Guide d'installation complet pour Ubuntu Server |
| **[DOCKER-README.md](DOCKER-README.md)** | 🐳 Installation alternative avec Docker |
| **backend/README.md** | 📦 Documentation backend Laravel |

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
php artisan migrate --seed

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
pnpm dev
# → http://localhost:5173

# Build de production
pnpm build
```

---

## 🔑 Connexion par défaut

Après avoir exécuté les seeders :

- **Email** : `admin@example.com`
- **Mot de passe** : `password`

⚠️ **Changez ces identifiants en production !**

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

### Option 1 : Installation native (recommandé)
Voir [UBUNTU-INSTALL.md](UBUNTU-INSTALL.md) et [INSTALL-README.md](INSTALL-README.md)

### Option 2 : Docker
Voir [DOCKER-README.md](DOCKER-README.md)

### Configuration serveur web

**Nginx** - Configuration recommandée dans [INSTALL-README.md](INSTALL-README.md)

**Apache** - Configuration avec mod_rewrite dans [INSTALL-README.md](INSTALL-README.md)

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

### Documentation
- [Guide d'installation Ubuntu](UBUNTU-INSTALL.md)
- [Docker](DOCKER-README.md)
- [API Documentation](http://localhost:8000/docs) (après installation)

### Communauté
- **Issues** : [GitHub Issues](https://github.com/IN-AFRICA/PGFE_INSTALL/issues)
- **Discussions** : [GitHub Discussions](https://github.com/IN-AFRICA/PGFE_INSTALL/discussions)

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

## 🙏 Remerciements

- [Laravel](https://laravel.com)
- [Vue.js](https://vuejs.org)
- [Tailwind CSS](https://tailwindcss.com)
- [Spatie](https://spatie.be/open-source)
- Tous les contributeurs

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
