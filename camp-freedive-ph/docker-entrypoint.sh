#!/bin/sh
set -e

# Run migrations and seeders on startup
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Ensure sqlite file exists
if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
fi

# Run migrations and seed data
php artisan migrate --force --seed

# Optimize caches for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Determine port (Render provides $PORT, defaults to 8080 or 10000)
PORT=${PORT:-8080}

echo "Starting Laravel server on port $PORT..."
exec php -S 0.0.0.0:$PORT -t public
