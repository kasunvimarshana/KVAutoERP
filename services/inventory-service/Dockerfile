FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libssl-dev \
    librabbitmq-dev \
    zip \
    unzip \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        zip \
        sockets \
    && pecl install redis amqp \
    && docker-php-ext-enable redis amqp \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy dependency manifests first to leverage Docker layer caching
COPY composer.json composer.json
RUN composer install --no-dev --no-scripts --no-autoloader --optimize-autoloader --prefer-dist

# Copy application source
COPY . .

# Generate optimised autoload and run post-install scripts
RUN composer dump-autoload --no-dev --optimize \
    && composer run-script post-autoload-dump || true

# Set correct ownership
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

EXPOSE 8003

CMD ["php-fpm"]
