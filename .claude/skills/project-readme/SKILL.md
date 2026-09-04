---
name: project-readme
description: Rédaction et mise à jour du README de rendu, y compris la section obligatoire sur l'usage des outils d'IA.
when_to_use: Avant de créer ou mettre à jour le README, ou à la clôture d'une phase quand l'état du projet a changé.
---

Le README est un livrable de premier plan. Le cahier des charges retient comme critère « clarté du README et facilité à faire tourner le projet ».

## Plan

1. **Présentation** — ce que fait l'outil, en trois phrases, du point de vue d'un agent du service client.
2. **Stack** — Laravel, PHP, Vue, TypeScript, Inertia, PostgreSQL, Pest, avec les versions réelles (lues dans `composer.json` / `package.json`, jamais devinées).
3. **Prérequis** — versions PHP, Composer, Node, base de données.
4. **Installation locale** — bloc de commandes copiables, du clone jusqu'à l'application accessible. Testé, pas supposé.
5. **Configuration `.env`** — variables à renseigner et leur rôle. Aucune valeur réelle.
6. **Migrations et données de démo** — `php artisan migrate --seed`.
7. **Lancement** — `composer run dev`.
8. **Tests** — `php artisan test`.
9. **Lint / format** — `vendor/bin/pint`, `npm run lint`, `npm run format`.
10. **Structure du projet** — les dossiers qui comptent, une ligne chacun.
11. **Choix architecturaux** — pourquoi Inertia plutôt qu'une SPA séparée, pourquoi Fortify via le starter kit officiel, pourquoi PostgreSQL, pourquoi une table pivot pour les étiquettes. Deux à quatre lignes par choix.
12. **Fait / pas fait / simplifié** — trois listes explicites, avec la raison. Le sujet le demande nommément.
13. **Application en ligne** — URL Laravel Cloud + identifiants du compte de démonstration.
14. **Usage des outils d'IA** — section obligatoire.

## Section « Usage des outils d'IA »

Le cahier des charges la demande explicitement. Elle doit être concrète et honnête :

- quel outil, pour quoi (génération de squelettes, migrations, composants, tests, revue) ;
- ce qui a été **repris ou corrigé à la main**, et pourquoi — c'est le passage qui montre le jugement technique ;
- comment le périmètre a été tenu (contraintes de projet, revue systématique avant commit).

Trois à dix lignes suffisent. Une section vague ou flatteuse n'apporte rien.

## Règle de sincérité

Ne jamais documenter une fonctionnalité non implémentée. Un README qui promet ce que l'application ne fait pas est un défaut plus grave qu'une fonctionnalité manquante assumée.
