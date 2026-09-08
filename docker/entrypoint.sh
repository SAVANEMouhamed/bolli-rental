#!/bin/sh
# Point d'entrée unique des trois rôles du conteneur. Le rôle est porté par
# CONTAINER_ROLE ; sans lui, le conteneur sert l'application.
#
# Les caches de configuration, de routes et de vues sont reconstruits au démarrage
# et non à la construction de l'image : ils dépendent de l'environnement, qui
# n'est connu qu'ici.

set -eu

role="${CONTAINER_ROLE:-app}"

# Une commande explicite l'emporte sur le rôle : `docker run <image> php artisan
# about` doit exécuter cette commande, pas démarrer l'application. Les rôles ne
# s'appliquent donc qu'au démarrage sans argument, tel que le fait la composition.
if [ "$#" -gt 0 ]; then
    exec "$@"
fi

log() {
    printf '[entrypoint][%s] %s\n' "$role" "$1" >&2
}

wait_for_database() {
    attempt=0
    # PDO plutôt que pg_isready : le premier vérifie aussi les identifiants et
    # l'existence de la base, le second se contente de constater qu'un serveur
    # écoute. C'est bien la connexion applicative qui doit être prête.
    until php -r '
        try {
            new PDO(
                sprintf("pgsql:host=%s;port=%s;dbname=%s", getenv("DB_HOST"), getenv("DB_PORT") ?: "5432", getenv("DB_DATABASE")),
                getenv("DB_USERNAME"),
                getenv("DB_PASSWORD"),
                [PDO::ATTR_TIMEOUT => 3]
            );
        } catch (Throwable $e) {
            exit(1);
        }
    ' 2>/dev/null
    do
        attempt=$((attempt + 1))
        if [ "$attempt" -ge 30 ]; then
            log 'base de données injoignable après 30 tentatives, abandon'
            exit 1
        fi
        log "base de données indisponible, nouvelle tentative ($attempt/30)"
        sleep 2
    done
    log 'base de données prête'
}

build_caches() {
    # config:cache d'abord : les commandes suivantes lisent la configuration.
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
}

case "$role" in
    app)
        wait_for_database
        php artisan config:cache

        log 'migrations'
        php artisan migrate --force --no-interaction

        # Les seeders sont idempotents : ils créent le jeu de démonstration au
        # premier déploiement et se contentent ensuite de remettre le mot de passe
        # du compte de recette à la valeur publiée dans le README.
        if [ "${SEED_ON_DEPLOY:-true}" = "true" ]; then
            log 'jeu de démonstration'
            php artisan db:seed --force --no-interaction
        fi

        build_caches
        log 'démarrage de PHP-FPM'
        exec php-fpm --nodaemonize
        ;;

    queue)
        wait_for_database
        build_caches
        log 'démarrage du worker de queue'
        # --max-time recycle le processus toutes les heures : un worker de longue
        # durée finit par tenir en mémoire un état devenu faux.
        exec php artisan queue:work \
            --tries=3 \
            --backoff=10 \
            --max-time=3600 \
            --sleep=3 \
            --no-interaction
        ;;

    web)
        log 'démarrage de nginx'
        exec nginx -g 'daemon off;'
        ;;

    *)
        log "rôle inconnu : $role"
        exit 1
        ;;
esac
