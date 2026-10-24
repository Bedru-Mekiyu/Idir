#!/bin/sh
set -e

# Default Render port is 10000 if not provided
PORT="${PORT:-10000}"
echo "Configuring Nginx to listen on port: ${PORT}"
sed -i "s/PORT_PLACEHOLDER/${PORT}/g" /etc/nginx/http.d/default.conf

# Ensure write permissions for Laravel storage and cache
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Ensure APP_KEY is available
if [ -z "$APP_KEY" ]; then
    echo "Warning: APP_KEY not provided in environment. Generating a new key..."
    export APP_KEY=$(php artisan key:generate --show --no-ansi)
fi

# Ensure .env exists in container and contains the APP_KEY
if [ ! -f /var/www/html/.env ]; then
    touch /var/www/html/.env
fi

if ! grep -q "^APP_KEY=" /var/www/html/.env 2>/dev/null; then
    echo "APP_KEY=${APP_KEY}" >> /var/www/html/.env
else
    sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" /var/www/html/.env
fi

# Handle SQLite database file if used
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    DB_FILE="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
    if [ ! -f "$DB_FILE" ]; then
        echo "Creating SQLite database file at ${DB_FILE}..."
        mkdir -p "$(dirname "$DB_FILE")"
        touch "$DB_FILE"
    fi
    chown -R www-data:www-data "$(dirname "$DB_FILE")"
    chmod -R 775 "$(dirname "$DB_FILE")"
fi

# Clear any previous cached configurations before caching fresh ones
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Link storage
php artisan storage:link --force || true

# Discover packages
php artisan package:discover --ansi || true

# Run database migrations if enabled
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force || echo "Warning: Migration execution returned non-zero status."
fi

# In production, cache config, routes, and views
if [ "${APP_ENV:-production}" = "production" ]; then
    echo "Caching config, routes, and views for production..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
    php artisan filament:cache-components || true
fi

echo "Starting Nginx and PHP-FPM via Supervisord..."
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
