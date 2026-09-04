---
name: setup-phase1
description: Runbook d'initialisation du projet Laravel + Vue + Inertia + TypeScript pour Bolli Rental — phase 1, fondations uniquement.
disable-model-invocation: true
argument-hint: '[nom-du-dossier]'
allowed-tools: Read Write Edit Glob Grep Bash(php:*) Bash(composer:*) Bash(npm:*) Bash(git:*) Bash(vendor/bin/pint:*) Bash(laravel:*)
---

Phase 1 = fondations. **Aucune fonctionnalité métier.** Pas de dashboard, pas de CRUD appels, pas de filtres, pas de statistiques, aucun bonus. Voir `docs/internal/cadrage-technique.md` §5, §28, §29.

## 0. Vérifications préalables

```bash
php -v            # 8.3 minimum, 8.4 recommandé
composer -V
node -v && npm -v # Node 20+
```

Si PHP ou Composer manquent sur la machine, **s'arrêter et le signaler** avant toute autre action. Sur macOS, la voie la plus simple est [Laravel Herd](https://herd.laravel.com) qui installe PHP, Composer et la CLI Laravel.

Vérifier ensuite la dernière version stable de Laravel et sa contrainte PHP avant d'installer — ne pas se fier à une version mémorisée.

## 1. Création du projet

Le projet vit dans le dossier courant, qui contient déjà `.claude/` et `docs/`. Générer dans un dossier temporaire puis rapatrier, pour ne rien écraser.

```bash
composer global require laravel/installer
laravel new bolli-tmp --vue --database=pgsql --pest --npm --git=false --no-boost
```

Puis déplacer le contenu de `bolli-tmp/` (fichiers cachés compris) à la racine du projet et supprimer `bolli-tmp/`.

Ce que cela installe : Laravel 13, Inertia 3, Vue 3 Composition API, TypeScript, Tailwind 4, shadcn-vue, Wayfinder, Laravel Fortify pour l'authentification, Pest.

Ne pas installer Breeze ni Jetstream : gelés depuis Laravel 12, le starter kit Vue officiel est leur remplaçant et satisfait le « ou équivalent » du cahier des charges.

## 2. Git — point de vigilance

Ce dossier est actuellement **à l'intérieur du dépôt Git du dossier personnel**. Il lui faut son propre dépôt, sinon le rendu GitHub est impossible.

```bash
git init -b main
git status   # doit lister uniquement les fichiers de ce projet
```

Vérifier que `.gitignore` couvre `.env`, `/vendor`, `/node_modules`, `/public/build`, `/storage/*.key`, `.phpunit.result.cache`.

## 3. Base de données

PostgreSQL en local si disponible, sinon SQLite pour démarrer — les deux sont autorisés par le cahier des charges, et le passage à PostgreSQL sur Laravel Cloud se fait par variables d'environnement. Documenter le choix.

```bash
php artisan migrate
```

## 4. Laravel Boost

```bash
composer require laravel/boost --dev
php artisan boost:install     # sélectionner Claude Code, guidelines + skills
```

Ajouter `.mcp.json` et `boost.json` au `.gitignore` (régénérables). Garder `.ai/rules/` en versionné si Boost en crée.

## 5. Authentification

- Ouvrir `config/fortify.php`. Outil **interne** : désactiver `Features::registration()` et créer les agents par seeder. Retirer aussi les références aux routes d'inscription côté Vue, sinon le build Wayfinder casse.
- Vérifier le rate limiting du login dans `FortifyServiceProvider`.
- Renforcer la règle de mot de passe dans `app/Actions/Fortify/PasswordValidationRules.php` : `min(12)` et `uncompromised()`.

## 6. Modèle de données

Migrations, modèles, enums, relations, factories et seeders selon le skill `eloquent-database`. Créer la structure complète, **sans contrôleur métier ni écran**.

```bash
php artisan migrate:fresh --seed
```

## 7. Qualité

```bash
vendor/bin/pint          # PHP
npm run lint             # ESLint
npm run format           # Prettier
npm run build            # le build doit passer
php artisan test         # la suite doit être verte
```

Activer `Model::preventLazyLoading(! app()->isProduction())` dans `AppServiceProvider::boot()` : les N+1 deviennent des erreurs dès le développement.

## 8. Documents

`.env.example` complet et sans valeur réelle. README initial via le skill `project-readme`, décrivant l'état réel du projet et rien de plus.

## 9. Commits

Commits progressifs au fil de l'avancement, via `/git-commits`. Jamais un seul commit final.

## 10. Clôture

Invoquer `/phase-gate`, produire le rapport, puis **s'arrêter et attendre l'accord** du responsable du projet après son push GitHub.
