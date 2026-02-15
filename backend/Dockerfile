# ── Stage 1 : Installation des dépendances PHP ──
FROM composer:2 AS composer

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-autoloader --no-scripts --no-progress

# ── Stage 2 : Build des assets frontend (si nécessaire) ──
FROM node:22-alpine AS node-builder

WORKDIR /app
COPY package*.json ./
RUN npm ci --only=production

COPY . .
RUN npm run build 2>/dev/null || echo "No npm build script found"

# ── Stage 3 : Runtime PHP Production ──
FROM php:8.2-fpm-alpine AS production

# Installation des extensions PHP requises par Laravel et les packages utilisés
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    libxml2-dev \
    icu-dev \
    chromium \
    chromium-chromedriver \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mysqli \
        gd \
        zip \
        mbstring \
        xml \
        bcmath \
        intl \
        exif \
    && rm -rf /var/cache/apk/*

# Configuration optimisée PHP pour production
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN echo "memory_limit = 512M" > /usr/local/etc/php/conf.d/memory-limit.ini && \
    echo "upload_max_filesize = 50M" > /usr/local/etc/php/conf.d/upload.ini && \
    echo "post_max_size = 50M" >> /usr/local/etc/php/conf.d/upload.ini && \
    echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/upload.ini

# Créer utilisateur non-root pour la sécurité
RUN addgroup -g 1000 -S www && \
    adduser -u 1000 -S www -G www

WORKDIR /var/www/html

# Copier les dépendances depuis le stage composer
COPY --from=composer /app/vendor ./vendor
COPY --from=node-builder /app/public ./public

# Copier le code source Laravel
COPY --chown=www:www . .

# Finaliser l'installation Composer avec autoloader optimisé
RUN composer dump-autoload --no-dev --optimize && \
    composer clear-cache

# Créer les dossiers de cache et logs avec bonnes permissions
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache && \
    chown -R www:www /var/www/html && \
    chmod -R 755 /var/www/html/storage

# Variables d'environnement pour Browsershot (utilisé par spatie/browsershot)
ENV CHROME_BIN=/usr/bin/chromium-browser \
    CHROME_PATH=/usr/bin/chromium-browser \
    PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true

# Cache des configurations Laravel pour performance
RUN php artisan config:cache 2>/dev/null || true && \
    php artisan route:cache 2>/dev/null || true && \
    php artisan view:cache 2>/dev/null || true

# Healthcheck Laravel
HEALTHCHECK --interval=30s --timeout=10s --start-period=30s --retries=5 \
    CMD php artisan about --no-ansi || exit 1

USER www

EXPOSE 8000

# Serveur Laravel intégré (pour développement/test) ou PHP-FPM
CMD php artisan serve --host=0.0.0.0 --port=8000