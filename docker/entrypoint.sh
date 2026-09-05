#!/usr/bin/env bash
set -e

cd /var/www/html

echo "== Preparing storage/cache directories =="
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "== Linking public storage =="
php artisan storage:link || true

echo "== Clearing any stale cached config (env vars only exist at runtime, not build time) =="
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "== Re-caching config/routes/views with live environment variables =="
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set RUN_MIGRATIONS=true in Railway/Render's environment variables to run
# migrations automatically on every deploy. Leave unset/false to run them
# manually instead (safer for production once the schema stabilizes).
if [ "${RUN_MIGRATIONS}" = "true" ]; then
    echo "== Running migrations =="
    php artisan migrate --force
fi

echo "== Rendering nginx config for PORT=${PORT:-8080} =="
export PORT="${PORT:-8080}"
envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

echo "== Boot complete, handing off to: $* =="
exec "$@"
