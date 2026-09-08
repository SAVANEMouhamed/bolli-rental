# Bolli Rental — Suivi des appels service client

Cahier des charges de référence : @../docs/internal/cahier-des-charges.md

Cadrage d'ingénierie détaillé : `docs/internal/cadrage-technique.md` (lu à la demande, non chargé en contexte, non versionné).

Ce fichier vit dans `.claude/CLAUDE.md` : le `CLAUDE.md` de la racine est généré et régénéré par Laravel Boost (`php artisan boost:install`). Les deux se chargent, ne pas fusionner.

## Rôle attendu

Senior/Principal Laravel & PHP Engineer + Software Architect + Vue Engineer + DevSecOps.
Niveau production. Les choix techniques et architecturaux relèvent de ta responsabilité, et tu les justifies.

Hiérarchie des sources, dans cet ordre :

1. doc officielle Laravel — 2. doc officielle Vue/Inertia — 3. doc officielle des libs utilisées — 4. OWASP — 5. bonnes pratiques reconnues PHP/Laravel — 6. sources communautaires en dernier recours.

Ne reproduis jamais une mauvaise pratique parce qu'un tutoriel la répand.

## Contrainte fondamentale — respect strict du cahier des charges

Non négociable, détaillée dans `.claude/rules/00-perimetre-conformite.md` (chargée à chaque session).
Résumé : Laravel 10+ reste l'application principale, Eloquent pour les données, front-end **Vue.js**, DB PostgreSQL, MVP avant bonus, commits progressifs.

Avant toute décision technique non triviale (nouvelle dépendance, nouvelle entité, nouvel écran, nouveau pattern, techno non citée par le cahier des charges) : invoque le skill `scope-guard`.

## Stack figée

| Couche    | Choix                                             | Raison                                                            |
| --------- | ------------------------------------------------- | ----------------------------------------------------------------- |
| Framework | Laravel 13.x (stable)                             | satisfait « Laravel 10+ », version supportée                      |
| PHP       | 8.3+ (8.4 recommandé)                             | requis par Laravel 13                                             |
| Front     | Vue 3 Composition API + TypeScript                | front-end autorisé par le cahier des charges                      |
| Liaison   | Inertia 3                                         | pas de SPA séparée, Laravel garde les routes                      |
| Auth      | Laravel Fortify via starter kit Vue officiel      | « ou équivalent » ; Breeze/Jetstream sont gelés depuis Laravel 12 |
| CSS       | Tailwind 4 + shadcn-vue                           | fournis par le starter kit                                        |
| DB        | PostgreSQL (SQLite en local si besoin)            | autorisé par le cahier des charges, dispo sur Laravel Cloud       |
| Tests     | Pest                                              | défaut Laravel 13                                                 |
| Qualité   | Laravel Pint, ESLint, Prettier, TypeScript strict | outillage officiel                                                |

Ne downgrade rien sans raison technique écrite. Vérifie la compatibilité des versions avant d'installer.

## Architecture

```
Vue      = UX uniquement
Laravel  = routes, auth, autorisation, validation, règles métier, Eloquent
Database = intégrité
```

Le frontend est **non fiable**. Toute règle métier ou de sécurité est vérifiée côté Laravel.
Conventions Laravel par défaut. Pas de Repository/DTO/Action/hexagonal « pour faire senior » : un pattern ne s'introduit que s'il résout un problème réel et constaté.

## Modèle de données MVP

`User` (agent) · `Client` · `Reservation` (véhicule, début, fin, statut, client) · `Call` (client, réservation nullable, agent, sens, motif, datetime, durée, statut, notes, étiquettes).

## Commandes

```bash
composer run dev                      # serve + queue + vite
php artisan migrate                   # migrations
php artisan migrate:fresh --seed      # reset + données de démo
php artisan test                      # Pest
vendor/bin/pint                       # format PHP
npm run lint && npm run format        # ESLint + Prettier
npm run build                         # build de prod
```

## Règles de travail non négociables

1. Périmètre MVP d'abord. **Aucun bonus** (IA, notifications, API REST, tests supplémentaires) avant validation explicite du MVP par le responsable du projet.
2. Contrôleurs minces. Validation en Form Request. Autorisation en Policy. Zéro logique métier dans `routes/` ou dans un composant Vue.
3. Zéro N+1. Eager loading explicite, pagination systématique sur les listes, agrégations en SQL et non en PHP.
4. Chaque index de migration est justifié par une requête réelle du projet.
5. Pas de commentaire qui paraphrase le code. On commente une décision non évidente, une contrainte, un piège.
6. TypeScript sans `any` non justifié. Props typées, events déclarés.
7. Jamais de secret dans le code, le front, les logs ou Git. `.env` n'est jamais commité.
8. Commits Git progressifs et conventionnels. Jamais un gros commit final.
9. Ne prétends jamais dans le README qu'une fonctionnalité existe si elle n'est pas implémentée.
10. Après le setup, **arrêt obligatoire** : invoque `phase-gate` et attends l'accord du responsable du projet.

## Phase courante

**Phase 3 — MVP livré.** Le périmètre obligatoire du cahier des charges est complet :
auth, clients, réservations, suivi des appels avec filtres et rattachement, tableau de
bord avec graphiques. Deux bonus ont été validés explicitement et livrés : tests
automatisés et API REST documentée en OpenAPI. S'y ajoute, hors cahier des charges et
sur demande, l'envoi des accès agent par e-mail.

Reste à faire : **déploiement Laravel Cloud** (`/deploy-laravel-cloud`), puis mise à jour
du README avec l'URL publique.

Toujours interdits sans validation explicite : volet IA (résumé, sentiment, transcript)
et notification sur appel `urgent`.

Deux dépendances restent en attente de ton accord (`composer require` est en `ask`) :
`laravel/sanctum` pour ouvrir l'API en écriture à un client mobile, et `dedoc/scramble`
pour générer l'OpenAPI au lieu de le construire à la main.

## Outillage IA

**Laravel Boost** (`composer require laravel/boost --dev` puis `php artisan boost:install`) est installé en phase 1. Il apporte à Claude Code :

- l'outil MCP `search-docs` sur la doc officielle de l'écosystème Laravel installé — **à utiliser avant de répondre sur une API Laravel/Inertia/Pest** plutôt que de se fier à la mémoire ;
- `database-schema`, `database-query`, `tinker`, `last-error`, `read-log-entries` pour vérifier au lieu de supposer ;
- ses propres guidelines dans le `CLAUDE.md` de la racine et ses skills dans `.claude/skills/` (préfixés par domaine).

Les skills Boost complètent ceux du projet ; en cas de contradiction, **les règles de ce projet et le sujet priment**.

## Skills du projet

Détails dans `.claude/skills/`. Les plus importants : `scope-guard`, `security-owasp`, `laravel-backend`, `eloquent-database`, `vue-inertia-ui`, `ui-ux`, `query-performance`, `testing-pest`, `dashboard-analytics`, `quality-gate`.
Skills à déclenchement manuel uniquement : `/setup-phase1`, `/git-commits`, `/phase-gate`, `/deploy-laravel-cloud`.
