---
paths:
    - 'tests/**/*.php'
---

# Règles tests

- Pest, syntaxe fonctionnelle. `RefreshDatabase` pour tout test qui touche la base.
- On teste le comportement observable, pas l'implémentation : un test ne doit pas casser lors d'un refactor à comportement constant.
- Arrangement via factories. Aucune donnée fixée en dur qui dépend de l'ordre d'exécution.
- Couverture prioritaire : authentification · création d'appel · validation · autorisation (un agent ne voit/modifie que ce qu'il doit) · filtres de liste · rattachement à une réservation · agrégations du dashboard.
- Tester systématiquement le chemin refusé : non authentifié → redirection, non autorisé → 403, payload invalide → 422 avec les bons champs en erreur.
- Un test = une assertion de comportement claire. Nom explicite en français ou en anglais, mais cohérent dans tout le projet.
