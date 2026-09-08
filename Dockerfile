# syntax=docker/dockerfile:1.11

# Image de production de Bolli Rental.
#
# Deux étages : « build » fabrique, « runtime » exécute. L'étage de construction
# porte Composer, Node et les dépendances de compilation ; rien de tout cela
# n'atteint l'image finale, qui ne contient que le code, le vendor de production
# et les assets compilés.
#
# Une seule image sert trois rôles — PHP-FPM, nginx, worker de queue — pilotés par
# la variable CONTAINER_ROLE. C'est délibéré : le serveur web sert les assets
# compilés, qui doivent provenir exactement du même build que le code PHP. Deux
# images séparées finiraient tôt ou tard désynchronisées.

ARG PHP_VERSION=8.4
ARG COMPOSER_VERSION=2.8

# `COPY --from` n'interpole pas les ARG dans une référence d'image : le binaire
# Composer passe donc par un étage nommé.
FROM composer/composer:${COMPOSER_VERSION}-bin AS composer

##############################################################################
# Étage 1 — construction
##############################################################################
FROM php:${PHP_VERSION}-cli-alpine AS build

COPY --from=composer /composer /usr/bin/composer

# Node est requis ici et pas seulement pour Vite : le plugin Wayfinder appelle
# `php artisan wayfinder:generate` pendant `npm run build`. La compilation des
# assets a donc besoin de PHP, du vendor et de Node dans le même étage.
RUN apk add --no-cache git unzip nodejs npm

WORKDIR /app

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

# Dépendances d'abord, code ensuite : un commit qui ne touche pas aux verrous
# réutilise ces deux couches telles quelles.
COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/tmp/composer-cache \
    COMPOSER_CACHE_DIR=/tmp/composer-cache \
    composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY package.json package-lock.json ./
RUN --mount=type=cache,target=/root/.npm \
    npm ci --no-audit --no-fund

COPY . .

# La clé est une valeur factice, inline et jetable : artisan est appelé ici
# (package:discover, wayfinder) et refuse de démarrer sans. Ni ARG ni ENV, pour
# qu'elle n'entre pas dans les métadonnées de l'image. La vraie clé vient de
# l'environnement au démarrage du conteneur.
RUN APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= sh -eux -c '\
    composer dump-autoload --no-dev --optimize --classmap-authoritative; \
    php artisan package:discover --ansi; \
    npm run build; \
    rm -rf node_modules'

##############################################################################
# Étage 2 — exécution
##############################################################################
FROM php:${PHP_VERSION}-fpm-alpine AS runtime

# Extensions : pdo_pgsql pour la base, intl pour les dates et la comparaison de
# chaînes, zip pour Composer, bcmath pour les entiers longs, opcache pour la
# performance, pcntl pour que `queue:work` s'arrête proprement sur SIGTERM.
# icu-libs, libpq et libzip sont installés séparément des paquets -dev : ces
# derniers sont supprimés après compilation, et ils emporteraient les bibliothèques
# partagées avec eux. Les extensions ne se chargeraient alors plus au démarrage.
RUN apk add --no-cache nginx tini icu-libs libpq libzip \
 && apk add --no-cache --virtual .build-deps ${PHPIZE_DEPS} icu-dev libzip-dev libpq-dev \
 && docker-php-ext-install -j"$(nproc)" pdo_pgsql intl zip bcmath opcache pcntl \
 && apk del .build-deps \
 && rm -rf /var/cache/apk/*

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php-fpm-pool.conf /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint

WORKDIR /var/www/html

COPY --from=build --chown=www-data:www-data /app /var/www/html

# nginx et PHP-FPM tournent sans privilèges. nginx écoute donc sur 8080 : un port
# inférieur à 1024 exigerait root. Les répertoires d'exécution de nginx doivent
# appartenir à l'utilisateur, sinon il refuse de démarrer.
RUN chmod +x /usr/local/bin/entrypoint \
 && mkdir -p /var/lib/nginx/tmp /var/log/nginx /run/nginx \
 && chown -R www-data:www-data /var/lib/nginx /var/log/nginx /run/nginx \
 && chown -R www-data:www-data storage bootstrap/cache

USER www-data

EXPOSE 8080 9000

# tini récolte les processus zombies : PHP-FPM et nginx en engendrent, et un PID 1
# qui ne les récolte pas finit par saturer la table des processus.
ENTRYPOINT ["/sbin/tini", "--", "/usr/local/bin/entrypoint"]
