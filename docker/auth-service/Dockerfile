# ────────────────────────────────────────────────────────────────────
# Stage 1: Build dependencies
# ────────────────────────────────────────────────────────────────────
FROM php:8.2-fpm-alpine AS builder

WORKDIR /app

# Install build dependencies
RUN apk add --no-cache \
        git \
        unzip \
        curl \
        libzip-dev \
        libpng-dev \
        oniguruma-dev \
        openssl-dev \
        icu-dev \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        zip \
        mbstring \
        bcmath \
        opcache \
        intl \
        pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis

# Copy Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Copy composer files first to leverage Docker layer caching
COPY composer.json composer.lock ./

# Install PHP dependencies (no dev packages in production)
RUN composer install \
        --no-dev \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader \
        --no-scripts

# Copy application source
COPY . .

# Run post-install scripts
RUN composer dump-autoload --optimize

# ────────────────────────────────────────────────────────────────────
# Stage 2: Production image
# ────────────────────────────────────────────────────────────────────
FROM php:8.2-fpm-alpine AS production

WORKDIR /app

# Install runtime dependencies only
RUN apk add --no-cache \
        libzip \
        libpng \
        oniguruma \
        openssl \
        icu-libs \
        nginx \
        supervisor \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        zip \
        mbstring \
        bcmath \
        opcache \
        intl \
        pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis

# Copy built artifacts from builder stage
COPY --from=builder /app /app

# OPcache configuration for production
COPY docker/auth-service/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Nginx and Supervisor configs
COPY docker/auth-service/nginx.conf /etc/nginx/nginx.conf
COPY docker/auth-service/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create required directories and set permissions
RUN mkdir -p \
        storage/app \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        storage/keys \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chmod 700 storage/keys

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=10s --start-period=30s --retries=3 \
    CMD curl -f http://localhost/api/v1/health || exit 1

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
