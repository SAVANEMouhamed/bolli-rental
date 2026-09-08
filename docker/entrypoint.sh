#!/bin/sh
set -eu

# Une commande explicite l'emporte, pour garder l'image inspectable.
[ "$#" -gt 0 ] && exec "$@"

probe_database() {
    php -r '
        try {
            new PDO(
                sprintf("pgsql:host=%s;port=%s;dbname=%s", getenv("DB_HOST"), getenv("DB_PORT") ?: "5432", getenv("DB_DATABASE")),
                getenv("DB_USERNAME"),
                getenv("DB_PASSWORD"),
                [PDO::ATTR_TIMEOUT => 3]
            );
        } catch (Throwable $e) {
            fwrite(STDERR, $e->getMessage());
            exit(1);
        }
    ' 2>&1
}

target="${DB_USERNAME:-?}@${DB_HOST:-?}:${DB_PORT:-5432}/${DB_DATABASE:-?}"
attempt=0

# L'erreur PDO est reprise telle quelle : « name does not resolve » désigne un
# réseau mal joint, « authentication failed » des identifiants faux, « does not
# exist » une base absente. Sans elle, les trois cas se ressemblent.
until error=$(probe_database); do
    attempt=$((attempt + 1))
    if [ "$attempt" -eq 1 ] || [ $((attempt % 10)) -eq 0 ]; then
        echo "base injoignable sur ${target} (${attempt}/30) : ${error}" >&2
    fi
    if [ "$attempt" -ge 30 ]; then
        echo "abandon : la base ${target} est restée injoignable pendant 60 s" >&2
        exit 1
    fi
    sleep 2
done

php artisan config:cache
php artisan migrate --force --no-interaction

# Seeders idempotents : ils créent le jeu de démonstration au premier démarrage,
# puis se contentent de remettre le compte de recette à l'état publié.
if [ "${SEED_ON_DEPLOY:-true}" = "true" ]; then
    php artisan db:seed --force --no-interaction
fi

# Ce que contient réellement la base après migrations et seeders. Sans cette
# ligne, « je ne vois pas mes données » ne peut se trancher qu'en ouvrant un
# client SQL, et rien ne dit sur quelle base on a semé.
php artisan tinker --execute 'printf(
    "contenu de la base %s : %d agents, %d clients, %d reservations, %d appels\n",
    DB::connection()->getDatabaseName(),
    App\Models\User::count(),
    App\Models\Client::count(),
    App\Models\Reservation::count(),
    App\Models\Call::count()
);' 2>/dev/null || true

php artisan route:cache
php artisan view:cache
php artisan event:cache

exec frankenphp run --config /etc/frankenphp/Caddyfile
