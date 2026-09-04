---
paths:
    - 'database/migrations/**/*.php'
    - 'app/Models/**/*.php'
---

# Règles schéma & Eloquent

- Chaque relation a une clé étrangère déclarée avec un `onDelete` explicite et réfléchi.
- Types adaptés : `duration_seconds` en entier (pas de chaîne), horodatage en `timestampTz`, montants en `decimal`.
- Colonnes `NOT NULL` par défaut ; la nullabilité est un choix explicite (ex. `reservation_id` nullable car un appel n'est pas forcément lié à une réservation).
- **Chaque index est justifié par une requête réelle.** Écrire la requête visée en commentaire dans la migration. Aucun index décoratif.
- Index composite : ordre des colonnes = ordre de sélectivité pour les filtres réellement combinés (ex. `(status, called_at)`, `(user_id, called_at)`).
- Mass assignment : `$fillable` explicite. Jamais `$guarded = []`.
- `$casts` pour enums, dates, booléens, JSON.
- Une factory par modèle, avec des states nommés pour les cas de test.
- Scopes uniquement quand ils suppriment une duplication réelle de requête.
- Interdit dans un modèle : requêtes en boucle, logique de présentation, appel HTTP.
