# syntax=docker/dockerfile:1.11

ARG PHP_VERSION=8.4
ARG COMPOSER_VERSION=2.8

FROM composer/composer:${COMPOSER_VERSION}-bin AS composer

# ---------------------------------------------------------------- construction
FROM php:${PHP_VERSION}-cli-alpine AS build

COPY --from=composer /composer /usr/bin/composer

# Node vit dans cet étage parce que `npm run build` déclenche le plugin Wayfinder,
# qui appelle `php artisan wayfinder:generate` : la compilation des assets exige
# PHP et le vendor.
RUN apk add --no-cache git unzip nodejs npm

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

WORKDIR /app

COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/tmp/composer-cache \
    COMPOSER_CACHE_DIR=/tmp/composer-cache \
    composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY package.json package-lock.json ./
RUN --mount=type=cache,target=/root/.npm \
    npm ci --no-audit --no-fund

COPY . .

# Clé jetable, ni ARG ni ENV pour qu'elle n'entre pas dans les métadonnées :
# artisan est appelé ici et refuse de démarrer sans.
RUN APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= sh -eux -c '\
      composer dump-autoload --no-dev --optimize --classmap-authoritative; \
      php artisan package:discover --ansi; \
      npm run build; \
      rm -rf node_modules'

# -------------------------------------------------------------------- exécution
FROM dunglas/frankenphp:php${PHP_VERSION}-alpine AS runtime

RUN install-php-extensions pdo_pgsql

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/Caddyfile /etc/frankenphp/Caddyfile
COPY --chmod=755 docker/entrypoint.sh /usr/local/bin/entrypoint

WORKDIR /app
COPY --from=build --chown=www-data:www-data /app ./

# Caddy écrit son état dans les répertoires XDG et refuse de démarrer s'il ne
# peut pas les créer. Le port 8080 évite d'avoir à conserver CAP_NET_BIND_SERVICE.
ENV XDG_CONFIG_HOME=/config \
    XDG_DATA_HOME=/data \
    SERVER_NAME=:8080

RUN mkdir -p /config /data \
 && chown -R www-data:www-data /config /data storage bootstrap/cache

USER www-data
EXPOSE 8080

ENTRYPOINT ["entrypoint"]
