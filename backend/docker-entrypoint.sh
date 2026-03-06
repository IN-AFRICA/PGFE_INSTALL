#!/bin/sh
set -e

echo "=== PGFE Backend Entrypoint ==="

ENV_FILE="/var/www/html/.env"

echo "[entrypoint] Creation du fichier .env..."
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

CURRENT_KEY=$(grep "^APP_KEY=" "$ENV_FILE" | cut -d'=' -f2-)
if [ -z "$CURRENT_KEY" ]; then
    echo "[entrypoint] Génération APP_KEY..."
    php artisan key:generate --force --no-interaction
else
    echo "[entrypoint] APP_KEY définie."
fi

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
        echo "[entrypoint] WARN: DB non joignable après ${MAX_RETRIES} tentatives."
        break
    fi
    echo "[entrypoint] DB pas encore prête ($RETRY/$MAX_RETRIES)..."
    sleep 2
done

echo "[entrypoint] Migrations..."
php artisan migrate --force --no-interaction 2>&1 \
    && echo "[entrypoint] Migrations OK." \
    || echo "[entrypoint] WARN: migrations échouées."

SEEDER_MARKER="/var/www/html/storage/.seeded"
if [ ! -f "$SEEDER_MARKER" ]; then
    echo "[entrypoint] Seeders..."
    if php artisan db:seed --force --no-interaction 2>&1; then
        touch "$SEEDER_MARKER"
        echo "[entrypoint] Seeders OK."
    else
        echo "[entrypoint] WARN: Seeders échoués."
    fi
fi

echo "[entrypoint] Cache..."
php artisan config:cache --no-interaction 2>/dev/null || true
php artisan route:cache  --no-interaction 2>/dev/null || true
php artisan view:cache   --no-interaction 2>/dev/null || true

chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

echo "=== Entrypoint OK, démarrage php-fpm ==="
exec "$@"
