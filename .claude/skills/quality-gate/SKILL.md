---
name: quality-gate
description: Revue de qualité interne avant de considérer un lot de travail terminé — conformité au cahier des charges, architecture, sécurité, performance, tests, lisibilité.
when_to_use: Avant d'annoncer qu'une fonctionnalité ou une phase est terminée, avant un commit important, ou quand l'utilisateur demande une relecture, un audit ou « est-ce que c'est propre ».
allowed-tools: Read Grep Glob Bash(git diff:*) Bash(git status:*) Bash(vendor/bin/pint --test:*) Bash(php artisan test:*) Bash(npm run lint:*)
---

Passer les sept portes dans l'ordre. Chaque porte rend un verdict : **OK**, **à corriger**, ou **bloquant**. Une porte bloquante interrompt la revue.

## 1. Conformité au cahier des charges

Laravel au cœur · Eloquent pour les données · migrations pour le schéma · métier côté Laravel · Vue limité à l'UI · aucun bonus non validé. Doute ⇒ `scope-guard`.

## 2. Architecture

Contrôleurs minces · Form Requests · Policies · pas d'abstraction sans problème constaté · pas de God Object · pas de méthode de plus de ~40 lignes · pas de duplication évidente · configuration via `config/`.

## 3. Sécurité

Dérouler la checklist de `security-owasp` sur les fichiers modifiés. Points les plus souvent ratés : autorisation d'objet (IDOR), `validated()` vs `all()`, tri/filtre interpolé dans une requête brute, champ sensible assignable en masse.

## 4. Performance

Aucun N+1 · listes paginées · agrégats en SQL · aucune requête en boucle · colonnes lourdes exclues des listes.

## 5. Tests

Le périmètre critique de `testing-pest` est-il couvert pour ce lot ? Les chemins refusés sont-ils testés ? `php artisan test` passe-t-il ?

## 6. Lisibilité

Nommage explicite · aucun commentaire qui paraphrase le code · aucun `dd()`/`dump()`/`console.log` oublié · aucun `any` TypeScript injustifié · aucun fichier mort · code formaté (`vendor/bin/pint --test`, `npm run lint`).

## 7. Livrable

`.env` absent de Git · aucun secret · README à jour et **sincère** (rien d'annoncé qui n'existe pas) · commits progressifs et lisibles.

## Sortie

Un tableau `porte | verdict | motif`, puis la liste ordonnée des corrections à faire. Ne pas corriger sans y être invité, sauf pour un point bloquant de sécurité — dans ce cas, signaler et proposer le correctif.
