# syntax=docker/dockerfile:1

##################################################
# Stage 1: Install PHP dependencies
##################################################
FROM composer:2 AS vendor

WORKDIR /app

COPY database/ database/
COPY composer.json composer.lock ./

# no-scripts: artisan isn't available yet (no app code copied), so skip
# package:discover etc. here - it runs again after the full app is copied.
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

COPY . .

RUN composer dump-autoload --optimize --no-dev \
    && composer run-script post-autoload-dump --no-interaction || true

##################################################
# Stage 2: Build frontend assets (Vite/Vue/Tailwind)
##################################################
FROM node:20-alpine AS frontend

WORKDIR /app

ARG VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ARG VITE_REVERB_PORT
ARG VITE_REVERB_SCHEME

ENV VITE_REVERB_APP_KEY=$VITE_REVERB_APP_KEY
ENV VITE_REVERB_HOST=$VITE_REVERB_HOST
ENV VITE_REVERB_PORT=$VITE_REVERB_PORT
ENV VITE_REVERB_SCHEME=$VITE_REVERB_SCHEME

COPY package.json package-lock.json* ./
RUN npm ci

COPY . .

# resources/js/app.js imports "../../vendor/tightenco/ziggy" directly out of
# Composer's vendor directory (the standard Breeze/Inertia Ziggy setup,
# rather than the separate ziggy-js npm package). This stage never runs
# composer install, so without this, Vite can't resolve that import at all -
# grab just the one package Ziggy actually needs from the vendor stage.
COPY --from=vendor /app/vendor/tightenco/ziggy ./vendor/tightenco/ziggy

RUN npm run build

##################################################
# Stage 3: Runtime image
##################################################
FROM php:8.4-fpm-alpine AS runtime

RUN apk add --no-cache \
        nginx \
        supervisor \
        bash \
        curl \
        gettext \
        mysql-client \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libzip-dev \
        icu-dev \
        oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

WORKDIR /var/www/html

# App code + vendor from the composer stage
COPY --from=vendor /app /var/www/html

# Built frontend assets (manifest + hashed js/css) from the node stage
COPY --from=frontend /app/public/build /var/www/html/public/build

# Runtime config
COPY docker/nginx.conf.template /etc/nginx/nginx.conf.template
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-laravel.ini
COPY docker/entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Railway/Render both inject PORT at runtime; this is just documentation for `docker run` locally
EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
