#!/bin/sh
set -e

echo "=== PGFE Backend Entrypoint ==="

# ── 0. Créer le fichier .env si absent (nécessaire pour artisan) ──
ENV_FILE="/var/www/html/.env"
if [ ! -f "$ENV_FILE" ]; then
    echo "[entrypoint] Création du fichier .env depuis les variables d'environnement..."
    cat > "$ENV_FILE" <<EOF
APP_NAME=${APP_NAME:-PGFE}
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY:-}
APP_DEBUG=${APP_DEBUG:-false}
APP_TIMEZONE=${APP_TIMEZONE:-Africa/Kinshasa}
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

LOG_CHANNEL=${LOG_CHANNEL:-stack}
LOG_LEVEL=${LOG_LEVEL:-error}

BROADCAST_CONNECTION=${BROADCAST_CONNECTION:-log}
FILESYSTEM_DISK=${FILESYSTEM_DISK:-local}
EOF
    chown www:www "$ENV_FILE" 2>/dev/null || true
    echo "[entrypoint] .env créé."
else
    echo "[entrypoint] .env existant détecté."
fi

# ── 1. S'assurer que les permissions sont correctes ──
echo "[entrypoint] Vérification des permissions..."
mkdir -p /var/www/html/storage/logs \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# ── 2. Générer APP_KEY si absente ──
APP_KEY_VAL=$(grep "^APP_KEY=" "$ENV_FILE" | cut -d'=' -f2-)
if [ -z "$APP_KEY_VAL" ]; then
    echo "[entrypoint] APP_KEY absente, génération..."
    php artisan key:generate --force --no-interaction 2>&1 || echo "[entrypoint] WARN: key:generate a échoué"
else
    echo "[entrypoint] APP_KEY déjà définie."
fi

# ── 3. Attendre que la DB soit prête ──
echo "[entrypoint] Attente de la base de données..."
MAX_RETRIES=30
RETRY=0
DB_READY=false
while [ "$RETRY" -lt "$MAX_RETRIES" ]; do
    if php -r "
        \$h='${DB_HOST:-database}'; \$p=${DB_PORT:-3306};
        \$s=@fsockopen(\$h,\$p,\$en,\$es,2);
        if(\$s){fclose(\$s);exit(0);}exit(1);
    " 2>/dev/null; then
        DB_READY=true
        echo "[entrypoint] DB accessible."
        break
    fi
    RETRY=$((RETRY + 1))
    echo "[entrypoint] DB pas encore prête (tentative $RETRY/$MAX_RETRIES)..."
    sleep 2
done

if [ "$DB_READY" = "false" ]; then
    echo "[entrypoint] WARN: DB non joignable après ${MAX_RETRIES} tentatives, on continue..."
fi

# ── 4. Migrations ──
echo "[entrypoint] Lancement des migrations..."
php artisan migrate --force --no-interaction 2>&1 || echo "[entrypoint] WARN: Migration a échoué (première exécution ?)"

# ── 5. Cache de config (optimisation) ──
echo "[entrypoint] Optimisation du cache..."
php artisan config:cache --no-interaction 2>/dev/null || true
php artisan route:cache --no-interaction 2>/dev/null || true
php artisan view:cache --no-interaction 2>/dev/null || true

echo "=== Entrypoint terminé, lancement de php-fpm ==="

# Exécuter la commande passée (php-fpm par défaut)
exec "$@"
