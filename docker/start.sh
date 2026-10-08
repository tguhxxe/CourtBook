#!/bin/sh
set -eu
cd /var/www/html
: "${APP_KEY:?Isi APP_KEY pada Environment Render}"
: "${DB_URL:?Isi DB_URL dengan connection string Neon}"
[ "${DB_CONNECTION:-}" = "pgsql" ] || { echo "Deployment ini membutuhkan DB_CONNECTION=pgsql" >&2; exit 1; }
export APP_URL="${APP_URL:-${RENDER_EXTERNAL_URL:-}}"
: "${APP_URL:?Isi APP_URL dengan URL HTTPS website}"
export MIDTRANS_PUBLIC_URL="${MIDTRANS_PUBLIC_URL:-$APP_URL}"
case "${PORT:-10000}" in *[!0-9]*|'') echo "PORT harus berupa angka" >&2; exit 1;; esac
sed -i "s/^Listen .*/Listen ${PORT:-10000}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:10000>/<VirtualHost *:${PORT:-10000}>/" /etc/apache2/sites-available/000-default.conf
php artisan config:cache
php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction
php artisan courtbook:bootstrap-admin --no-interaction
php artisan route:cache
php artisan view:cache
chown -R www-data:www-data storage bootstrap/cache
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/courtbook.conf
