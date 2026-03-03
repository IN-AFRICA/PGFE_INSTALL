#!/bin/sh
set -e

echo "=== PGFE Backend Entrypoint ==="

# ── 1. Générer APP_KEY si absente ──
if [ -z "$APP_KEY" ]; then
    echo "[entrypoint] APP_KEY absente, génération..."
    php artisan key:generate --force --no-interaction
else
    echo "[entrypoint] APP_KEY déjà définie."
fi

# ── 2. Attendre que la DB soit prête ──
echo "[entrypoint] Attente de la base de données..."
MAX_RETRIES=30
RETRY=0
until php artisan db:monitor --databases=mysql 2>/dev/null; do
    RETRY=$((RETRY + 1))
    if [ "$RETRY" -ge "$MAX_RETRIES" ]; then
        echo "[entrypoint] WARN: DB non joignable après ${MAX_RETRIES} tentatives, on continue..."
        break
    fi
    echo "[entrypoint] DB pas encore prête (tentative $RETRY/$MAX_RETRIES)..."
    sleep 2
done

# ── 3. Migrations ──
echo "[entrypoint] Lancement des migrations..."
php artisan migrate --force --no-interaction 2>&1 || echo "[entrypoint] WARN: Migration a échoué (première exécution ?)"

# ── 4. Cache de config (optimisation production) ──
echo "[entrypoint] Optimisation du cache..."
php artisan config:cache --no-interaction 2>/dev/null || true
php artisan route:cache --no-interaction 2>/dev/null || true
php artisan view:cache --no-interaction 2>/dev/null || true

# ── 5. S'assurer que les permissions sont correctes ──
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

echo "=== Entrypoint terminé, lancement de php-fpm ==="

# Exécuter la commande passée (php-fpm par défaut)
exec "$@"
