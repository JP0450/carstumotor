FROM dunglas/frankenphp:latest-alpine

RUN apk add --no-cache bash git unzip libzip-dev oniguruma-dev icu-dev zlib-dev openssl postgresql-dev build-base nodejs npm python3 make g++
RUN install-php-extensions pdo pdo_pgsql mbstring intl zip bcmath

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

COPY composer.json composer.lock package.json artisan bootstrap ./

RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
RUN npm install

COPY . .

RUN npm run build

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 755 storage bootstrap/cache

ENV PORT=8080

EXPOSE 8080

CMD ["frankenphp", "run", "--bind", "0.0.0.0:8080"]

