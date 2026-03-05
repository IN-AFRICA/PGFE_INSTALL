#!/bin/sh
set -e

echo "=== PGFE Backend Entrypoint ==="

ENV_FILE="/var/www/html/.env"

# ── 1. Créer/reconstruire le .env depuis les variables Docker ──
# Le .env est exclu par .dockerignore → il faut le recréer à chaque démarrage
echo "[entrypoint] Création du fichier .env depuis les variables d'environnement..."
cat > "$ENV_FILE" << ENVEOF
APP_NAME=${APP_NAME:-PGFE}
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY:-}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-http://localhost}

DB_CONNECTION=${DB_CONNECTION:-mysql}
DB_HOST=${DB_HOST:-database}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-pgfe_db}
DB_USERNAME=${DB_USERNAME:-pgfe_user}
DB_PASSWORD=${DB_PASSWORD:-pgfe_secret}

SESSION_DRIVER=${SESSION_DRIVER:-file}
CACHE_STORE=${CACHE_STORE:-file}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}
LOG_CHANNEL=stack
LOG_LEVEL=error
ENVEOF
echo "[entrypoint] .env créé."

# ── 2. Générer APP_KEY si absente ──
CURRENT_KEY=$(grep "^APP_KEY=" "$ENV_FILE" | cut -d'=' -f2-)
if [ -z "$CURRENT_KEY" ]; then
    echo "[entrypoint] APP_KEY absente, génération..."
    php artisan key:generate --force --no-interaction
    echo "[entrypoint] APP_KEY générée."
else
    echo "[entrypoint] APP_KEY déjà définie."
fi

# ── 3. Attendre que la DB soit prête (connexion PDO directe, plus fiable) ──
echo "[entrypoint] Attente de la base de données..."
MAX_RETRIES=30
RETRY=0
until php -r "
\$h = getenv('DB_HOST') ?: 'database';
\$p = getenv('DB_PORT') ?: '3306';
\$u = getenv('DB_USERNAME') ?: 'pgfe_user';
\$w = getenv('DB_PASSWORD') ?: 'pgfe_secret';
\$d = getenv('DB_DATABASE') ?: 'pgfe_db';
try {
    \$c = new PDO(\"mysql:host=\$h;port=\$p;dbname=\$d\", \$u, \$w, [PDO::ATTR_TIMEOUT => 3]);
    \$c->query('SELECT 1');
    exit(0);
} catch (Exception \$e) { exit(1); }
" 2>/dev/null; do
    RETRY=$((RETRY + 1))
    if [ "$RETRY" -ge "$MAX_RETRIES" ]; then
        echo "[entrypoint] WARN: DB non joignable après ${MAX_RETRIES} tentatives, on continue..."
        break
    fi
    echo "[entrypoint] DB pas encore prête (tentative $RETRY/$MAX_RETRIES)..."
    sleep 2
done
echo "[entrypoint] DB prête (ou timeout dépassé)."

# ── 4. Migrations ──
echo "[entrypoint] Lancement des migrations..."
php artisan migrate --force --no-interaction 2>&1 && echo "[entrypoint] Migrations OK." || echo "[entrypoint] WARN: Migration a échoué."

# ── 5. Seeders (première exécution uniquement) ──
SEEDER_MARKER="/var/www/html/storage/.seeded"
if [ ! -f "$SEEDER_MARKER" ]; then
    echo "[entrypoint] Premier démarrage → exécution des seeders..."
    if php artisan db:seed --force --no-interaction 2>&1; then
        touch "$SEEDER_MARKER"
        echo "[entrypoint] Seeders OK."
    else
        echo "[entrypoint] WARN: Seeders échoués (relancez manuellement si besoin)."
    fi
else
    echo "[entrypoint] Seeders déjà exécutés."
fi

# ── 6. Cache de config (optimisation production) ──
echo "[entrypoint] Optimisation du cache..."
php artisan config:cache --no-interaction 2>/dev/null || true
php artisan route:cache  --no-interaction 2>/dev/null || true
php artisan view:cache   --no-interaction 2>/dev/null || true

# ── 7. Permissions storage ──
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

echo "=== Entrypoint terminé, lancement de php-fpm ==="

exec "$@"
