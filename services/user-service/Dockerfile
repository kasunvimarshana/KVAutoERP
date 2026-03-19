FROM php:8.3-fpm-alpine AS base

RUN apk add --no-cache \
    git curl libpng-dev libxml2-dev libzip-dev zip unzip openssl linux-headers \
    && docker-php-ext-install pdo pdo_mysql xml zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/pear

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ─── Dependencies ──────────────────────────────────────────────────────────
FROM base AS deps
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --optimize-autoloader

# ─── Application ───────────────────────────────────────────────────────────
FROM base AS app
COPY --from=deps /var/www/html/vendor ./vendor
COPY . .

RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

EXPOSE 8001

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s \
    CMD curl -f http://localhost:8001/api/v1/health || exit 1

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8001"]
