#!/bin/sh
set -e

# Cache config and routes for production performance
php artisan config:cache
php artisan route:cache

# Run database migrations automatically on start
php artisan migrate --force

# Start HTTP server on Render's PORT or default 8000
PORT=${PORT:-8000}
echo "Starting Laravel server on port $PORT..."
exec php artisan serve --host=0.0.0.0 --port=$PORT
