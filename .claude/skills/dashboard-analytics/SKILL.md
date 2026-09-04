---
name: dashboard-analytics
description: Construction du tableau de bord d'analyse des appels — agrégations SQL efficaces et intégration du graphique côté Vue.
when_to_use: Avant d'écrire une statistique, un compteur, un classement ou un graphique du dashboard. Aussi quand un calcul de statistique est écrit en PHP au lieu de SQL.
---

Le cahier des charges demande cinq choses : volume par jour/semaine, répartition par motif, répartition par statut, durée moyenne, classement des agents, plus au moins un graphique.

Règle absolue : **tout se calcule en base**. Charger les appels puis boucler en PHP est explicitement signalé comme à éviter par le cadrage du projet.

## Agrégations (PostgreSQL)

```php
// Volume par jour sur la période
Call::query()
    ->whereBetween('called_at', [$from, $to])
    ->selectRaw("date_trunc('day', called_at) as bucket, count(*) as total")
    ->groupBy('bucket')
    ->orderBy('bucket')
    ->get();

// Répartition par motif / par statut
Call::query()->whereBetween('called_at', [$from, $to])
    ->selectRaw('reason, count(*) as total')->groupBy('reason')->get();

// Durée moyenne
Call::query()->whereBetween('called_at', [$from, $to])->avg('duration_seconds');

// Classement des agents
Call::query()->whereBetween('called_at', [$from, $to])
    ->selectRaw('user_id, count(*) as total, avg(duration_seconds) as avg_duration')
    ->groupBy('user_id')->orderByDesc('total')->limit(10)
    ->with('user:id,name')->get();
```

- `date_trunc` avec `'day'` ou `'week'` selon la granularité demandée — un seul paramètre, validé contre une liste blanche, jamais interpolé depuis la requête HTTP.
- Les bornes `$from`/`$to` viennent d'une Form Request. Un dashboard sans borne de période finit par scanner toute la table.
- Ces requêtes s'appuient sur les index `calls(called_at)`, `calls(user_id, called_at)`, `calls(status, called_at)`, `calls(reason, called_at)`.
- Les jours sans appel n'apparaissent pas dans le résultat SQL : compléter la série côté PHP (remplissage des trous) avant de l'envoyer à Vue, sinon le graphique ment.

## Côté Vue

- Le contrôleur envoie des séries prêtes à tracer (`labels` + `datasets`), pas des lignes brutes à retravailler dans le composant.
- Chart.js via `vue-chartjs`, un composant `components/charts/` par type de graphique, props typées.
- Enregistrer uniquement les modules Chart.js utilisés (tree-shaking), pas `Chart.register(...registerables)`.
- Chaque graphique a un titre, des axes légendés et un équivalent accessible (tableau ou résumé textuel).
- Un état vide explicite quand la période ne contient aucun appel.

## Périmètre

Le dashboard fait partie du MVP obligatoire, mais **pas de la phase 1**. Ne le construire qu'après validation du setup.
