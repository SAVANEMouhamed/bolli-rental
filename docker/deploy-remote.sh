#!/usr/bin/env bash
# Exécuté sur le VPS, envoyé par la chaîne de déploiement sur l'entrée standard.
# Le jeton de registre arrive sur la première ligne de cette même entrée : il ne
# passe donc jamais par la ligne de commande, où il serait visible de tout
# utilisateur du serveur.

set -euo pipefail

read -r registry_token

cd "$TARGET"

printf '%s' "$registry_token" | docker login ghcr.io -u "$REGISTRY_USER" --password-stdin
unset registry_token

# Seule la ligne de l'image est réécrite dans le fichier d'environnement : les
# secrets du VPS ne transitent jamais par l'intégration continue.
if grep -q '^APP_IMAGE=' ".env"; then
    sed -i "s|^APP_IMAGE=.*|APP_IMAGE=${IMAGE}|" ".env"
else
    printf 'APP_IMAGE=%s\n' "$IMAGE" >> ".env"
fi

docker compose pull --quiet

# --wait rend la main seulement quand les conteneurs sont sains : si les
# migrations échouent, l'étape échoue au lieu de livrer une application morte.
docker compose up -d --remove-orphans --wait --wait-timeout 180

docker logout ghcr.io >/dev/null 2>&1 || true

# Les images antérieures à une semaine sont purgées : le disque d'un VPS se
# remplit vite, et deux ou trois versions suffisent pour revenir en arrière.
docker image prune --force --filter 'until=168h' >/dev/null

docker compose ps
