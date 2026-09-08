#!/bin/sh
set -eu

# Une commande explicite l'emporte, pour garder l'image inspectable.
[ "$#" -gt 0 ] && exec "$@"

attempt=0
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
' 2>/dev/null; do
    attempt=$((attempt + 1))
    [ "$attempt" -ge 30 ] && { echo 'base de données injoignable, abandon' >&2; exit 1; }
    echo "base de données indisponible ($attempt/30)" >&2
    sleep 2
done

php artisan config:cache
php artisan migrate --force --no-interaction

# Seeders idempotents : ils créent le jeu de démonstration au premier démarrage,
# puis se contentent de remettre le compte de recette à l'état publié.
if [ "${SEED_ON_DEPLOY:-true}" = "true" ]; then
    php artisan db:seed --force --no-interaction
fi

php artisan route:cache
php artisan view:cache
php artisan event:cache

exec frankenphp run --config /etc/frankenphp/Caddyfile
