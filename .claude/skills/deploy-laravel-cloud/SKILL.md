---
name: deploy-laravel-cloud
description: Prépare et vérifie le déploiement sur Laravel Cloud avec le compte de démonstration exigé par le rendu.
disable-model-invocation: true
allowed-tools: Read Edit Glob Grep Bash(php artisan:*) Bash(npm run build:*) Bash(git status:*) Bash(git log:*)
---

Le cahier des charges exige une application en ligne sur Laravel Cloud (offre gratuite), avec une URL publique et un compte de démonstration documenté dans le README. Sans cela, le rendu est incomplet.

## Avant de déployer

- [ ] `npm run build` passe.
- [ ] `php artisan test` est vert.
- [ ] `.env.example` liste toutes les variables nécessaires, sans valeur réelle.
- [ ] Aucun secret dans l'historique Git.
- [ ] Un seeder crée le compte de démonstration avec des identifiants fixes, et il est idempotent (`updateOrCreate`) pour survivre à un redéploiement.
- [ ] `config/app.php` : `APP_ENV=production`, `APP_DEBUG=false`.

## Configuration côté Laravel Cloud

- Connecter le dépôt GitHub, brancher sur `main`.
- Provisionner la base **Serverless Postgres** ; les variables de connexion sont injectées automatiquement.
- Commande de build : installation Composer + `npm ci && npm run build`.
- Commande de déploiement : `php artisan migrate --force`. Exécuter le seeder de démonstration une fois, ou le rendre idempotent et le laisser dans le déploiement.
- Générer `APP_KEY` si la plateforme ne le fait pas.
- Session et cookies : `SESSION_SECURE_COOKIE=true`, HTTPS forcé.

## Points de vigilance de l'offre gratuite

- L'instance et la base **hibernent** après inactivité : la première requête après une période creuse peut être lente. Le mentionner dans le README évite un malentendu.
- Pas de domaine personnalisé sur le palier gratuit ; l'URL fournie par la plateforme fait le rendu.
- Le crédit gratuit est limité : surveiller la consommation pour que l'application reste accessible dans la durée.

## Après déploiement

1. Ouvrir l'URL en navigation privée.
2. Se connecter avec le compte de démonstration.
3. Parcourir chaque écran du MVP et vérifier qu'ils affichent des données réelles.
4. Vérifier qu'aucune page d'erreur n'expose de trace.
5. Mettre l'URL et les identifiants de démonstration dans le README.
