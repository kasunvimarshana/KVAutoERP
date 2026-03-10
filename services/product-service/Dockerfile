FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    nginx supervisor git curl zip unzip \
    libzip-dev icu-dev oniguruma-dev libxml2-dev

RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath intl opcache
RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction 2>/dev/null || true

RUN mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf

EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
