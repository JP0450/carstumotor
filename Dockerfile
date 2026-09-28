# syntax=docker/dockerfile:1
FROM composer:2.8 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction
COPY . .
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci --ignore-scripts || npm install --ignore-scripts
COPY . .
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

FROM php:8.3-cli-alpine
RUN apk add --no-cache postgresql-dev libzip-dev icu-dev bash \
    && docker-php-ext-install pdo_pgsql pdo_mysql intl zip bcmath opcache \
    && rm -rf /var/cache/apk/*

WORKDIR /app
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build
COPY . .

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

ENV PORT=10000
EXPOSE 10000

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 CMD wget -qO- http://127.0.0.1:${PORT:-10000}/up || exit 1

CMD sh -c "php artisan migrate --force --no-interaction || true; php artisan config:cache; php artisan route:cache; php artisan view:cache; php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"
