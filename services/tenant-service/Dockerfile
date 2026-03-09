FROM php:8.2-fpm

# System dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    libpq-dev \
    libmemcached-dev \
    zip \
    unzip \
    procps \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    xml \
    zip \
    opcache \
    sockets

# Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# PCOV for code coverage
RUN pecl install pcov \
    && docker-php-ext-enable pcov

# Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# PHP configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Generate key if not present
RUN php artisan key:generate --force 2>/dev/null || true

EXPOSE 9000

CMD ["php-fpm"]
