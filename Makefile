.PHONY: help setup up down logs build clean

SHELL := /bin/bash
MODE ?= prod

# ── Variables ──
DOCKER_COMPOSE = docker compose
DOCKER_COMPOSE_PROD = $(DOCKER_COMPOSE) -f docker-compose.yml
DOCKER_COMPOSE_DEV = $(DOCKER_COMPOSE) -f docker-compose.yml -f docker-compose.dev.yml

# Sélectionner la bonne commande compose selon le mode
COMPOSE := $(if $(filter dev,$(MODE)), $(DOCKER_COMPOSE_DEV), $(DOCKER_COMPOSE_PROD))

# ── Help ──
help:
	@echo "╔════════════════════════════════════════════════════════════════╗"
	@echo "║          PGFE Docker Make Commands                            ║"
	@echo "╚════════════════════════════════════════════════════════════════╝"
	@echo ""
	@echo "USAGE: make [TARGET] [MODE=prod|dev]"
	@echo ""
	@echo "TARGETS:"
	@echo "  setup              Initialiser le projet (copier .env, générer APP_KEY)"
	@echo "  up                 Démarrer les services"
	@echo "  down               Arrêter les services"
	@echo "  restart            Redémarrer les services"
	@echo "  logs               Afficher les logs (dernières 50 lignes)"
	@echo "  logs-f             Afficher les logs en continu"
	@echo "  ps                 État des services"
	@echo "  build              Reconstruire les images Docker"
	@echo "  migrate            Lancer les migrations"
	@echo "  seed               Lancer les seeders"
	@echo "  bash-api           Accès bash au backend"
	@echo "  bash-db            Accès à MySQL"
	@echo "  clean              Arrêter et supprimer tous les conteneurs"
	@echo "  clean-volume       Supprimer aussi les données (⚠️ DANGER)"
	@echo ""
	@echo "MODES:"
	@echo "  prod (défaut)      Production"
	@echo "  dev                Développement (volumes mounted)"
	@echo ""
	@echo "EXEMPLES:"
	@echo "  make setup"
	@echo "  make up MODE=dev"
	@echo "  make logs-f MODE=dev"
	@echo "  make bash-api MODE=dev"
	@echo ""

# ── Setup ──
setup:
	@echo "📦 Configuration initiale du projet PGFE..."
	@if [ ! -f .env ]; then \
		echo "Copie de .env.example vers .env..."; \
		cp .env.example .env; \
		echo "✓ .env créé"; \
	else \
		echo ".env existe déjà"; \
	fi
	@echo "Génération de APP_KEY..."
	@docker run --rm --entrypoint php php:8.3-alpine -r "echo 'base64:' . base64_encode(random_bytes(32));" > /tmp/key.txt || true
	@echo "✓ Setup initial terminé"
	@echo "⚠️  N'oubliez pas de vérifier les paramètres dans .env"

# ── Services Management ──
up:
	@echo "🚀 Démarrage des services (mode: $(MODE))..."
	$(COMPOSE) up -d
	@echo "✓ Services démarrés"
	@echo ""
	@echo "Attendez quelques secondes pour que les healthchecks passent..."
	@sleep 3
	@make ps

down:
	@echo "🛑 Arrêt des services..."
	$(COMPOSE) down
	@echo "✓ Services arrêtés"

restart:
	@echo "🔄 Redémarrage des services..."
	$(COMPOSE) restart
	@echo "✓ Services redémarrés"

logs:
	$(COMPOSE) logs --tail=50

logs-f:
	@echo "📋 Logs en continu (Ctrl+C pour quitter)..."
	$(COMPOSE) logs -f

ps:
	$(COMPOSE) ps

# ── Build ──
build:
	@echo "🔨 Reconstruction des images Docker..."
	$(COMPOSE) up -d --build
	@echo "✓ Images reconstruites et services démarrés"

# ── Database ──
migrate:
	@echo "🗃️  Exécution des migrations..."
	$(COMPOSE) exec backend php artisan migrate --force

seed:
	@echo "🌱 Exécution des seeders..."
	$(COMPOSE) exec backend php artisan db:seed --force

reset-db:
	@echo "⚠️  Réinitialisation complète de la base de données..."
	$(COMPOSE) exec backend php artisan migrate:fresh --seed --force
	@echo "✓ Base de données réinitialisée"

# ── Access ──
bash-api:
	@echo "🐚 Accès bash au backend Laravel..."
	$(COMPOSE) exec backend bash

bash-db:
	@echo "🐚 Accès à MySQL..."
	$(COMPOSE) exec database mysql -u pgfe_user -p$${DB_PASSWORD:-pgfe_secret} pgfe_db

# ── Cache & Optimization ──
cache-clear:
	@echo "🗑️  Vidage du cache..."
	$(COMPOSE) exec backend php artisan cache:clear
	$(COMPOSE) exec backend php artisan config:clear

cache-build:
	@echo "⚡ Reconstruction du cache..."
	$(COMPOSE) exec backend php artisan config:cache
	$(COMPOSE) exec backend php artisan route:cache

# ── Cleanup ──
clean:
	@echo "🧹 Nettoyage des conteneurs..."
	$(COMPOSE) down
	@echo "✓ Conteneurs arrêtés et supprimés"

clean-volumes:
	@echo "⚠️  ATTENTION: Cela va supprimer TOUTES les données de la base de données!"
	@read -p "Tapez 'yes' pour confirmer: " confirm; \
	if [ "$$confirm" = "yes" ]; then \
		echo "Suppression des volumes..."; \
		$(COMPOSE) down -v; \
		echo "✓ Tous les conteneurs et volumes supprimés"; \
	else \
		echo "Opération annulée"; \
	fi

# ── Status Check ──
status:
	@echo "📊 État du projet PGFE"
	@echo ""
	@echo "Conteneurs:"
	@make ps
	@echo ""
	@echo "Volumes:"
	@docker volume ls | grep pgfe || echo "  (aucun volume)"
	@echo ""
	@echo "Networks:"
	@docker network ls | grep pgfe || echo "  (aucun réseau)"
