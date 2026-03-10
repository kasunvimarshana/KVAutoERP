#!/bin/sh
set -e

# Copy .env if not present
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Wait for DB to be ready
echo "[user-service] Waiting for database..."
until php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null; do
    echo "[user-service] Database not ready, retrying in 2s..."
    sleep 2
done
echo "[user-service] Database is ready."

# Run migrations
php artisan migrate --force

# Start built-in PHP server
exec php -S 0.0.0.0:8000 -t public
