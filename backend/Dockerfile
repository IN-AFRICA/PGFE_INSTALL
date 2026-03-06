FROM php:8.3-alpine AS php-builder

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apk add --no-cache \
    libpng-dev libjpeg-turbo-dev freetype-dev libexif-dev libzip-dev zlib-dev unzip

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd exif zip

WORKDIR /app
COPY composer.json composer.lock ./

RUN /usr/bin/composer install \
    --no-dev \
    --no-autoloader \
    --no-scripts \
    --no-progress \
    --ignore-platform-reqs

FROM node:22-alpine AS node-builder

WORKDIR /app
COPY package*.json ./

RUN npm config set fetch-retry-maxtimeout 1200000 && \
    npm config set fetch-retries 10 && \
    npm config set fetch-retry-mintimeout 100000 && \
    npm ci --omit=dev

COPY . .
RUN npm run build 2>/dev/null || echo "No npm build script found"

FROM php:8.3-fpm-alpine AS production

RUN apk add --no-cache \
    git curl zip unzip \
    libpng-dev libjpeg-turbo-dev freetype-dev \
    libzip-dev libexif-dev \
    oniguruma-dev libxml2-dev icu-dev \
    mariadb-client \
    chromium chromium-chromedriver \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql mysqli gd zip mbstring xml bcmath intl exif opcache \
    && apk del --no-cache \
        libpng-dev libjpeg-turbo-dev freetype-dev \
        libzip-dev libexif-dev \
        oniguruma-dev libxml2-dev icu-dev \
    && apk add --no-cache \
        libpng libjpeg-turbo freetype \
        libzip libexif \
        oniguruma libxml2 icu-libs \
    && rm -rf /var/cache/apk/*

RUN echo "memory_limit = 512M"       >  /usr/local/etc/php/conf.d/docker-php-memlimit.ini \
    && echo "upload_max_filesize = 50M" >  /usr/local/etc/php/conf.d/docker-php-upload.ini \
    && echo "post_max_size = 50M"       >> /usr/local/etc/php/conf.d/docker-php-upload.ini \
    && echo "opcache.enable_cli=1"      >  /usr/local/etc/php/conf.d/docker-php-opcache.ini

RUN addgroup -g 1000 -S www && adduser -u 1000 -S www -G www

WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=php-builder /app/vendor ./vendor
COPY --from=node-builder /app/public ./public
COPY --chown=www:www . .

RUN /usr/bin/composer dump-autoload --no-dev --optimize && \
    mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache && \
    chown -R www:www /var/www/html && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

ENV CHROME_BIN=/usr/bin/chromium-browser \
    CHROME_PATH=/usr/bin/chromium-browser \
    PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]