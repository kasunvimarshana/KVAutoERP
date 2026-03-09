# ─── Stage 1: Composer dependencies ──────────────────────────────────────────
FROM php:8.3-fpm AS builder

# Install system dependencies required to build PHP extensions.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libonig-dev \
        libxml2-dev \
        libssl-dev \
        libpq-dev \
        librabbitmq-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions.
RUN docker-php-ext-install \
        pdo \
        pdo_mysql \
        bcmath \
        mbstring \
        tokenizer \
        xml \
        ctype \
        opcache \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis

# Install Composer.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy dependency manifests first to leverage Docker layer cache.
COPY composer.json composer.lock* ./

# Install production dependencies only.
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --optimize-autoloader \
    && composer clear-cache

# Copy application source.
COPY . .

# Also copy the shared contracts (mounted via build context or volume).
COPY ../../shared /app/../../shared/ 2>/dev/null || true

# Generate the optimised autoloader.
RUN composer dump-autoload --optimize --classmap-authoritative


# ─── Stage 2: Production image ────────────────────────────────────────────────
FROM php:8.3-fpm AS production

LABEL maintainer="KV_SAAS Platform Team"
LABEL service="auth-service"
LABEL version="1.0.0"

# Runtime system deps.
RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev \
        libonig-dev \
        libxml2-dev \
        libssl-dev \
    && rm -rf /var/lib/apt/lists/*

# Install the same PHP extensions as builder (without build tools).
RUN docker-php-ext-install \
        pdo \
        pdo_mysql \
        bcmath \
        mbstring \
        tokenizer \
        xml \
        ctype \
        opcache \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis

# PHP-FPM & OPcache tuning.
COPY docker/php/php.ini   /usr/local/etc/php/conf.d/app.ini
COPY docker/php/fpm.conf  /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/html

# Copy the compiled application from the builder stage.
COPY --from=builder --chown=www-data:www-data /app .

# Ensure writable directories exist.
RUN mkdir -p storage/logs storage/framework/{cache,sessions,views} bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Switch to non-root user.
USER www-data

EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD php-fpm -t || exit 1

CMD ["php-fpm"]
