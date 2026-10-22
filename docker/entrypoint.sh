#!/bin/sh
set -eu

if [ "${1:-}" = "serve" ]; then
    if [ ! -f /data/.env ]; then
        cp /app/.env.example /data/.env
    fi

    # Keep the same encryption key across rebuilds and container recreation.
    if ! grep -q '^APP_KEY=base64:.' /data/.env; then
        php artisan key:generate --force --no-interaction
    fi

    # Do not truncate an existing database, or reseed over user changes.
    if [ ! -f /data/database.sqlite ]; then
        touch /data/database.sqlite
    fi
    php artisan view:clear
    php artisan migrate --force --no-interaction
    if [ ! -f /data/.seeded ]; then
        php artisan db:seed --force --no-interaction
        touch /data/.seeded
    fi

    exec php artisan serve --host=0.0.0.0 --port=8000 --no-reload
fi

exec "$@"
