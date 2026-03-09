# Inventory Service Dockerfile
# Multi-stage build for production optimization

FROM php:8.3-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    bash \
    curl \
    git \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    libzip-dev \
    mysql-client \
    oniguruma-dev \
    openssl \
    unzip \
    zip \
    && docker-php-ext-install \
        bcmath \
        exif \
        gd \
        mbstring \
        opcache \
        pdo \
        pdo_mysql \
        zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ============================================================
# Development stage
# ============================================================
FROM base AS development

COPY . .
RUN composer install --no-scripts --no-autoloader
RUN composer dump-autoload --optimize

EXPOSE 8002
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8002"]

# ============================================================
# Production stage
# ============================================================
FROM base AS production

ENV APP_ENV=production
ENV LOG_CHANNEL=stderr

# Copy application files
COPY . .

# Install production dependencies only
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    && composer dump-autoload --optimize --classmap-authoritative

# Configure PHP OPcache for production
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost:8002/api/health || exit 1

EXPOSE 8002
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8002"]
