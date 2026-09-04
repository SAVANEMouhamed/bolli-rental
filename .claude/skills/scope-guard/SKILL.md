---
name: scope-guard
description: Gate de conformité au cahier des charges Bolli Rental. Vérifie qu'une décision reste dans le périmètre imposé par le cahier des charges avant de l'appliquer. Rend un verdict AUTORISÉ / À JUSTIFIER / REFUSÉ.
when_to_use: Avant d'installer une dépendance (composer require, npm install), de choisir une techno non citée par le cahier des charges, de créer une entité, une table ou un écran non prévus, d'introduire un design pattern, ou dès qu'une demande ressemble à un bonus (IA, résumé, sentiment, transcript, notification, API REST, mobile). Aussi quand l'utilisateur demande « est-ce dans le périmètre », « on peut ajouter X ».
allowed-tools: Read Grep Glob
---

Périmètre de référence : `.claude/rules/00-perimetre-conformite.md`. En cas de doute sur une formulation exacte, relire `docs/internal/cahier-des-charges.md`.

## Procédure

1. Classer la décision : **contrainte du cahier des charges** / **MVP obligatoire** / **bonus explicite** / **hors sujet**.
2. Passer le test de conformité en 6 points de la règle 00.
3. Vérifier la phase courante déclarée dans `CLAUDE.md` — une décision valide sur le fond peut être prématurée.
4. Rendre le verdict.

## Verdicts

**AUTORISÉ** — dans le périmètre, dans la phase courante, aucune contrainte violée. Poursuivre.

**À JUSTIFIER** — dans le périmètre mais introduit un coût (dépendance, complexité, pattern). Produire en trois lignes : le problème réel constaté, l'alternative native Laravel/Vue écartée et pourquoi, le coût de maintenance. Puis demander l'accord.

**REFUSÉ** — sort du périmètre, contourne une contrainte, ou appartient à une phase ultérieure. Dire pourquoi, proposer l'équivalent conforme, et ne pas l'implémenter.

## Refus systématiques

- SPA Vue autonome consommant une API REST séparée : Inertia couvre le besoin.
- ORM ou couche d'accès aux données autre qu'Eloquent pour le CRUD courant.
- Techno absente du cahier des charges et non nécessaire au MVP (Nuxt, GraphQL, Redis obligatoire, broker de messages, microservice, service externe payant).
- Repository / DTO / Action / hexagonal introduit sans problème constaté.
- Tout bonus avant validation explicite du MVP : IA (résumé, sentiment, transcript, audio), notification d'appel urgent, API REST publique, tests au-delà du périmètre critique.
- Downgrade de version sans raison technique écrite.

## Cas « bonus »

Répondre : « C'est un bonus explicite du cahier des charges. Le cahier des charges demande un MVP propre avant tout bonus, et la phase courante est <phase>. Je peux préparer l'architecture pour le rendre facile à ajouter (ex. colonne nullable, job stub), sans l'implémenter. Confirme si tu veux passer outre. »

Ne jamais implémenter un bonus « en passant » parce que c'est rapide.

## Format de sortie

Trois à six lignes maximum : verdict, motif, action recommandée. Pas de dissertation.
