---
name: phase-gate
description: Vérifie et clôture une phase du projet, puis produit le rapport de synthèse et s'arrête en attendant l'accord du responsable du projet.
disable-model-invocation: true
allowed-tools: Read Grep Glob Bash(php artisan:*) Bash(vendor/bin/pint:*) Bash(npm run:*) Bash(git status:*) Bash(git log:*) Bash(git diff:*)
---

Le responsable du projet valide chaque phase. Cette porte se franchit en vérifiant, en rapportant, puis en **s'arrêtant**.

## 1. Vérifications — exécuter, pas supposer

| #   | Vérification          | Commande                                                            |
| --- | --------------------- | ------------------------------------------------------------------- |
| 1   | L'application démarre | `composer run dev` (puis arrêter)                                   |
| 2   | Laravel répond        | `php artisan about`                                                 |
| 3   | Le front compile      | `npm run build`                                                     |
| 4   | Inertia rend une page | ouvrir `/login`                                                     |
| 5   | Authentification      | connexion avec le compte de démo                                    |
| 6   | Migrations            | `php artisan migrate:status`                                        |
| 7   | Modèles et relations  | `php artisan tinker` sur une relation clé                           |
| 8   | Seeders/factories     | `php artisan migrate:fresh --seed`                                  |
| 9   | Tests                 | `php artisan test`                                                  |
| 10  | Format et lint        | `vendor/bin/pint --test` puis `npm run lint`                        |
| 11  | Aucun secret          | `git status`, `.env` non suivi, `git log -p` sans credential        |
| 12  | Propreté              | aucun fichier mort, aucun `dd()`/`console.log`, aucun TODO orphelin |

Un échec se corrige avant de rapporter. Ne jamais annoncer « vérifié » ce qui n'a pas été exécuté ; si une vérification est impossible, le dire explicitement.

## 2. Rapport

Structure imposée par le cadrage du projet :

- **Stack choisie** — Laravel, PHP, Vue, TypeScript, Inertia, base de données, solution d'authentification, avec versions réelles.
- **Architecture** — pourquoi ce choix, en quelques lignes.
- **Structure** — les dossiers principaux créés.
- **Base de données** — modèles, relations, migrations, index importants.
- **Sécurité** — mesures effectivement en place.
- **Tests** — tests exécutés et résultat réel.
- **Commandes** — installer, lancer, migrer, seeder, tester, formater, lancer le front.
- **Git** — commits réalisés.
- **État**.

## 3. Clôture

Terminer par la ligne exacte, seule sur sa ligne :

**SETUP PHASE COMPLETE — WAITING FOR USER GITHUB PUSH AND APPROVAL.**

Puis s'arrêter. N'entamer aucune tâche de la phase suivante sans accord explicite, même si elle paraît évidente ou rapide.
