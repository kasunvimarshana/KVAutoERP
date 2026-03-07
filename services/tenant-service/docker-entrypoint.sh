#!/bin/sh
set -e

# Copy .env if not present
if [ ! -f /var/www/.env ]; then
    cp /var/www/.env.example /var/www/.env
fi

# Generate app key if not set
php artisan key:generate --no-interaction --force

# Run migrations
php artisan migrate --force --no-interaction

# Optimise application
php artisan config:cache
php artisan route:cache

exec "$@"
