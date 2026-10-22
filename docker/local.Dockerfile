# syntax=docker/dockerfile:1
FROM php:8.4-cli-bookworm AS dependencies
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git unzip libicu-dev libzip-dev \
    && docker-php-ext-install -j2 intl pcntl zip \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /app
ENV APP_ENV=local \
    APP_DEBUG=false \
    APP_URL=http://127.0.0.1:8000 \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/data/database.sqlite

COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts --no-autoloader

FROM node:22-bookworm-slim AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY vite.config.js ./
COPY resources ./resources
COPY --from=dependencies /app/vendor/laravel/framework/src/Illuminate/Pagination/resources/views ./vendor/laravel/framework/src/Illuminate/Pagination/resources/views
RUN npm run build

FROM dependencies AS app
COPY . .
COPY --from=assets /app/public/build ./public/build
RUN composer dump-autoload --optimize --no-interaction \
    && composer check-platform-reqs \
    && mkdir -p /data storage/framework/cache/data storage/framework/sessions \
        storage/framework/views storage/logs bootstrap/cache \
    && ln -s /data/.env /app/.env \
    && chown -R www-data:www-data /app /data \
    && chmod +x /app/docker/entrypoint.sh
USER www-data
EXPOSE 8000
ENTRYPOINT ["/app/docker/entrypoint.sh"]
CMD ["serve"]
