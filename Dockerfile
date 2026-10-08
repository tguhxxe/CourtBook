FROM node:22-bookworm-slim AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

FROM php:8.4-apache-bookworm
RUN apt-get update && apt-get install -y --no-install-recommends \
    libonig-dev libpq-dev libzip-dev libicu-dev libsqlite3-dev libcurl4-openssl-dev supervisor unzip \
    && docker-php-ext-install -j2 mbstring pdo_pgsql pdo_sqlite zip intl bcmath pcntl opcache curl \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
    && composer check-platform-reqs --no-dev \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache
COPY --from=assets /app/public/build ./public/build
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/courtbook.conf
RUN chmod +x docker/start.sh
ENV PORT=10000
EXPOSE 10000
CMD ["/var/www/html/docker/start.sh"]
