#!/bin/sh
set -e

# Clear existing cache and re-cache config/routes for production performance
php artisan config:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache

# Run database migrations and seed default products/users
php artisan migrate --force
php artisan db:seed --force

# Start HTTP server on Render's PORT or default 8000
PORT=${PORT:-8000}
echo "Starting Laravel server on port $PORT..."
exec php artisan serve --host=0.0.0.0 --port=$PORT
