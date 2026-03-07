#!/bin/sh
set -e

if [ ! -f /var/www/.env ]; then
    cp /var/www/.env.example /var/www/.env
fi

php artisan key:generate --no-interaction --force
php artisan migrate --force --no-interaction
php artisan config:cache
php artisan route:cache

exec "$@"
