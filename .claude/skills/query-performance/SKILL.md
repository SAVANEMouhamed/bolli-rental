---
name: query-performance
description: Performance des requêtes Laravel — élimination des N+1, eager loading, pagination, agrégations SQL, sélection de colonnes.
when_to_use: Avant d'écrire une liste, une boucle sur une collection, une statistique, ou tout code qui parcourt des relations. Aussi quand une page semble lente ou qu'une revue signale un N+1.
---

## Règles

1. Toute liste est paginée. `paginate()` + `withQueryString()`. Jamais `all()` ni `get()` sur une table qui grandit.
2. Toute relation affichée est chargée explicitement : `with(['client:id,full_name', 'user:id,name'])`. Colonnes ciblées, et la clé étrangère toujours incluse sinon la relation revient vide.
3. `with()` avant l'exécution, `load()` sur un modèle déjà chargé, `loadMissing()` quand la relation peut déjà être là.
4. Un compteur se fait avec `withCount()`, pas avec `->relation->count()` dans une boucle.
5. Un agrégat se fait en SQL : `COUNT`, `AVG`, `SUM`, `GROUP BY`, `DATE_TRUNC`. Jamais charger la collection puis boucler en PHP.
6. Aucune requête à l'intérieur d'une boucle `foreach`. Si tu en écris une, remonte-la en `whereIn` + `keyBy`.
7. `select()` dès qu'une table a des colonnes lourdes (`notes`, `transcript`) inutiles à l'écran.
8. Cache uniquement sur un calcul mesuré comme coûteux et rarement invalidé. Pas de cache « au cas où ».

## Détection

```bash
php artisan test                 # les tests couvrant les listes doivent rester rapides
```

Pendant le développement, activer `Model::preventLazyLoading()` en environnement local dans un service provider : un N+1 devient une exception au lieu d'un ralentissement silencieux.

## Anti-optimisation prématurée

Ne pas dénormaliser, ne pas ajouter de colonne calculée, ne pas introduire de cache tant qu'une requête réelle n'a pas été mesurée comme problématique. La lisibilité et les conventions Laravel priment sur une micro-optimisation.
