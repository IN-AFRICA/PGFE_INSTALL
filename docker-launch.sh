#!/bin/bash
# ── PGFE Docker Launch Script ──
# Gère le démarrage des services en mode développement ou production

set -euo pipefail

MODE="${1:-prod}"
COMMAND="${2:-up}"

# Couleurs pour l'output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# ── Fonctions ──
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[OK]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

show_help() {
    cat << EOF
╔════════════════════════════════════════════════════════════════╗
║          PGFE Docker Compose Launch Script                    ║
╚════════════════════════════════════════════════════════════════╝

USAGE:
    ./docker-launch.sh [MODE] [COMMAND]

MODES:
    prod        Production (images immuables, pas de volumes sources)
    dev         Développement (volumes mounted, debug activé)

COMMANDS:
    up          Démarrer les services en arrière-plan (défaut)
    up-debug    Démarrer en mode debug (logs en avant-plan)
    down        Arrêter les services
    restart     Redémarrer les services
    logs        Afficher les logs
    logs-f      Afficher les logs en continu
    ps          État des conteneurs
    exec-api    Accès bash au backend API
    exec-db     Accès bash à la base de données

EXEMPLES:
    # Production
    ./docker-launch.sh prod up
    ./docker-launch.sh prod logs-f
    
    # Développement
    ./docker-launch.sh dev up
    ./docker-launch.sh dev up-debug
    ./docker-launch.sh dev logs-f

INFOS APRÈS LANCEMENT:
    Frontend:  http://localhost (port 80)
    API:       http://localhost:8400/api (port 8400)
    Database:  localhost:3306

EOF
}

check_env() {
    if [ ! -f ".env" ]; then
        log_warn ".env non trouvé, création à partir de .env.example..."
        cp .env.example .env
        log_warn "Définissez APP_KEY dans .env avant de continuer"
    fi
    
    if [ -z "${APP_KEY:-}" ]; then
        log_warn "APP_KEY vide dans .env, Docker va le générer automatiquement"
    fi
}

get_compose_files() {
    if [ "$MODE" = "dev" ]; then
        echo "-f docker-compose.yml -f docker-compose.dev.yml"
    else
        echo "-f docker-compose.yml"
    fi
}

compose_cmd() {
    local files=$(get_compose_files)
    docker compose $files "$@"
}

# ── Main ──
case "${MODE:-help}" in
    prod|development)
        MODE="prod"
        ;;
    dev|development)
        MODE="dev"
        ;;
    help|-h|--help)
        show_help
        exit 0
        ;;
    *)
        log_error "Mode invalide: $MODE"
        echo "Modes acceptés: prod, dev"
        show_help
        exit 1
        ;;
esac

check_env

case "${COMMAND:-up}" in
    up)
        log_info "Démarrage des services en mode ${MODE}..."
        compose_cmd up -d
        log_success "Services démarrés"
        sleep 3
        compose_cmd ps
        echo ""
        log_info "╔════════════════════════════════════════════════════════════╗"
        log_info "║         PGFE Applications démarrées avec succès           ║"
        log_info "╚════════════════════════════════════════════════════════════╝"
        echo ""
        log_info "pour attendre le démarrage complet (vérifier les healthchecks):"
        log_info "  docker compose ps"
        echo ""
        log_info "Accédez aux services sur:"
        log_info "  🌐 Frontend:  http://localhost"
        log_info "  🔧 API:       http://localhost:8400/api"
        log_info "  🗄️  Database:  localhost:3306"
        ;;
    
    up-debug)
        log_info "Démarrage des services en mode ${MODE} (debug - logs en avant-plan)..."
        compose_cmd up
        ;;
    
    down)
        log_info "Arrêt des services..."
        compose_cmd down
        log_success "Services arrêtés"
        ;;
    
    restart)
        log_info "Redémarrage des services..."
        compose_cmd restart
        log_success "Services redémarrés"
        ;;
    
    logs)
        compose_cmd logs --tail=50
        ;;
    
    logs-f)
        log_info "Affichage des logs en continu (Ctrl+C pour quitter)..."
        compose_cmd logs -f
        ;;
    
    ps)
        compose_cmd ps
        ;;
    
    exec-api)
        log_info "Accès bash au backend Laravel..."
        compose_cmd exec backend bash
        ;;
    
    exec-db)
        log_info "Accès à la base de données MySQL..."
        compose_cmd exec database mysql -u pgfe_user -p${DB_PASSWORD:-pgfe_secret} pgfe_db
        ;;
    
    *)
        log_error "Commande invalide: $COMMAND"
        echo ""
        show_help
        exit 1
        ;;
esac
