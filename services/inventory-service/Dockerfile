# ─── Stage 1: Composer dependencies ──────────────────────────────────────────
FROM composer:2.7 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# ─── Stage 2: Production image ────────────────────────────────────────────────
FROM php:8.3-fpm-alpine AS production

LABEL maintainer="KV_SAAS Team"
LABEL service="inventory-service"

# System dependencies
RUN apk add --no-cache \
    bash \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    postgresql-dev \
    oniguruma-dev \
    icu-dev \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pdo_mysql \
        zip \
        mbstring \
        exif \
        pcntl \
        bcmath \
        intl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/cache/apk/*

WORKDIR /var/www/html

# Copy vendor from build stage
COPY --from=vendor /app/vendor ./vendor

# Copy application source
COPY . .

# PHP configuration
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

RUN cp .env.example .env 2>/dev/null || true \
    && php artisan config:cache --no-interaction 2>/dev/null || true \
    && php artisan route:cache  --no-interaction 2>/dev/null || true \
    && chown -R www-data:www-data /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
