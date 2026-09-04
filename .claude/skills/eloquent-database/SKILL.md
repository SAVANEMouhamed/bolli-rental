---
name: eloquent-database
description: Conception du schéma PostgreSQL et des modèles Eloquent de Bolli Rental — migrations, clés étrangères, index justifiés, relations, casts, factories.
when_to_use: Avant de créer ou modifier une migration, un modèle, une relation, un index, une factory ou un seeder. Aussi pour arbitrer un type de colonne, une nullabilité ou une stratégie d'étiquettes.
paths:
    - 'database/**/*.php'
    - 'app/Models/**/*.php'
---

## Entités et relations

```
User 1─n Call
Client 1─n Reservation
Client 1─n Call
Reservation 1─n Call        (call.reservation_id nullable)
Call n─n Tag                (table pivot call_tag)
```

`reservation_id` est nullable : le cahier des charges demande de pouvoir rattacher un appel à une réservation existante, pas de l'exiger.

## Colonnes de `calls`

| Colonne            | Type                   | Note                                                  |
| ------------------ | ---------------------- | ----------------------------------------------------- |
| `client_id`        | FK NOT NULL            | `restrictOnDelete` : on ne perd pas l'historique      |
| `reservation_id`   | FK nullable            | `nullOnDelete`                                        |
| `user_id`          | FK NOT NULL            | agent traitant, `restrictOnDelete`                    |
| `direction`        | string + enum cast     | `inbound` / `outbound`                                |
| `reason`           | string + enum cast     | `reservation`/`complaint`/`support`/`payment`/`other` |
| `status`           | string + enum cast     | `resolved`/`pending`/`escalated`                      |
| `called_at`        | `timestampTz` NOT NULL | date/heure de l'appel                                 |
| `duration_seconds` | `unsignedInteger`      | entier, jamais une chaîne                             |
| `notes`            | `text` nullable        |                                                       |

Étiquettes : table `tags` + pivot `call_tag`. Une colonne JSON est plus simple à écrire mais rend le filtrage et le comptage par étiquette coûteux et non indexables proprement — le dashboard et les filtres justifient le pivot.

## Index — chacun adossé à une requête du projet

| Index                       | Requête servie                                |
| --------------------------- | --------------------------------------------- |
| `calls(called_at)`          | volume par jour/semaine, tri de la liste      |
| `calls(user_id, called_at)` | filtre agent + période, classement des agents |
| `calls(status, called_at)`  | filtre statut + période                       |
| `calls(reason, called_at)`  | répartition par motif sur une période         |
| `calls(client_id)`          | appels d'un client                            |
| `calls(reservation_id)`     | appels d'une réservation                      |
| `reservations(client_id)`   | réservations d'un client                      |

Écrire la requête visée en commentaire au-dessus de l'index. Aucun index qui ne serve aucune requête listée ci-dessus. Ne pas ajouter d'index sur une colonne à très faible cardinalité seule.

## Modèles

- `$fillable` explicite, jamais `$guarded = []`.
- `casts()` : enums, `called_at` en `datetime`.
- Relations typées : `public function client(): BelongsTo`.
- Scope de filtrage unique pour la liste des appels (`scopeFilter`) qui applique agent/statut/motif/période à partir des données **déjà validées**.
- Aucune requête dans un accessor.

## Factories et seeders

- `ClientFactory`, `ReservationFactory` (states `active`, `completed`, `cancelled`), `CallFactory` (states par statut et par motif), `UserFactory`.
- Seeder de démo : 3-4 agents, 10-20 clients, 20-30 réservations, et assez d'appels étalés sur 6-8 semaines pour que le dashboard soit lisible.
- Dates réparties dans le passé, pas toutes le même jour, sinon les graphiques ne prouvent rien.
- Un compte de démonstration aux identifiants fixes et documentés dans le README — le sujet l'exige pour la recette.
