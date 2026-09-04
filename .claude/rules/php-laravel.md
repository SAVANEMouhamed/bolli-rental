---
paths:
    - 'app/**/*.php'
    - 'routes/**/*.php'
    - 'config/**/*.php'
    - 'database/**/*.php'
---

# Règles PHP / Laravel

- `declare(strict_types=1);` en tête de chaque fichier PHP applicatif.
- Types de propriétés, de paramètres et de retour partout. Pas de `mixed` par confort.
- Contrôleur mince : résoudre, déléguer, retourner. Un contrôleur qui dépasse ~120 lignes ou une action qui dépasse ~25 lignes signale une responsabilité mal placée.
- Validation : Form Request dédiée dès qu'il y a plus de deux règles ou une règle métier. Jamais de `$request->all()` passé à un `create()`/`update()`.
- Autorisation : Policy pour chaque modèle exposé. `authorize()` dans la Form Request ou `Gate`/middleware `can:` sur la route. L'absence d'un bouton côté Vue n'est jamais une protection.
- Injection de dépendances par le constructeur ou la signature de méthode. Pas de `new` sur un service, pas de façade dans une classe métier testable unitairement.
- Une classe Service ne se crée que si la logique est réutilisée, ou trop longue pour un contrôleur, ou testable isolément avec profit. Sinon, elle reste dans le modèle ou le contrôleur.
- `Enum` PHP natif pour les valeurs fermées (sens d'appel, motif, statut), avec `casts` Eloquent. Pas de chaînes magiques dispersées.
- Aucune logique métier dans `routes/`. Une route = un binding vers une action de contrôleur.
- Configuration via `config/` et `.env`. Jamais `env()` hors des fichiers de `config/`.
- Erreurs : exceptions typées, gestion propre, aucun détail interne renvoyé au client en production.
