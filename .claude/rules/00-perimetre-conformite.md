# Contrainte fondamentale — respect strict du cahier des charges

Cette règle prime sur toute autre considération technique, esthétique ou de productivité.

## Ce que le cahier des charges impose

- **Laravel 10+** comme framework backend et application principale.
- **PHP moderne**.
- **Eloquent ORM** pour tout accès aux données.
- Front-end au choix parmi **Blade, Vue.js, Livewire** → le choix retenu est **Vue.js**.
- Base parmi **MySQL, PostgreSQL, SQLite** → le choix retenu est **PostgreSQL**.
- Git avec des **commits clairs et progressifs**.
- Rendu : dépôt GitHub public + README + application déployée sur Laravel Cloud avec compte de démo.

## Test de conformité — à passer avant chaque décision technique

1. Laravel reste-t-il le cœur de l'application (routes, auth, autorisation, validation, métier) ?
2. Les données sont-elles manipulées via **Eloquent** ?
3. Le schéma est-il défini par des **migrations Laravel** ?
4. La logique métier reste-t-elle côté Laravel ?
5. Vue reste-t-il cantonné à l'interface utilisateur ?
6. Aucune techno ne contourne ni ne remplace une contrainte du cahier des charges ?

Une réponse « non » ⇒ la décision est refusée, ou elle exige une justification écrite explicite.

## Interdits structurels

- Créer deux applications indépendantes (backend API-only + SPA Vue autonome). Inertia est une couche de liaison, pas une frontière d'application.
- Remplacer Eloquent par du SQL brut ou un autre ORM pour le CRUD courant.
- Introduire une techno absente du cahier des charges sans justification (ex. Nuxt, GraphQL, Redis obligatoire, microservices).
- Sur-architecturer : Repository/DTO/Action/hexagonal ne s'introduisent que face à un problème constaté.
- Downgrader une version sans raison technique écrite.

## Priorité MVP

Périmètre obligatoire : auth agents · Client + Reservation (seeders 10-20 clients, 20-30 réservations) · CRUD et suivi des appels avec filtres agent/statut/motif/période, détail, rattachement réservation · tableau de bord (volume jour/semaine, répartition motif et statut, durée moyenne, classement agents, ≥ 1 graphique).

Bonus **en attente**, à ne pas développer avant validation explicite du MVP : IA (résumé/sentiment), notifications d'appel urgent, API REST, tests automatisés supplémentaires. L'architecture peut les rendre faciles à ajouter ; le code ne doit pas les anticiper.

Un MVP propre et déployé vaut mieux qu'une V2 ambitieuse et cassée. C'est une exigence explicite du cahier des charges.

## Gate de phase

Le responsable du projet valide chaque phase. À la fin d'une phase : vérifier, rapporter, **s'arrêter**. Ne pas enchaîner sur la phase suivante sans accord explicite.
