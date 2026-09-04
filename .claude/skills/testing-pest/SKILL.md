---
name: testing-pest
description: Stratégie et écriture des tests Pest pour Bolli Rental — quoi tester en priorité, comment, et ce qu'il ne faut pas tester.
when_to_use: Avant d'écrire un test, avant de clore une fonctionnalité, ou quand on se demande si une fonctionnalité est suffisamment couverte.
---

Pest, tests de fonctionnalité (HTTP) en priorité : ils prouvent le comportement réel de l'application, contrairement aux tests unitaires sur des classes sans logique.

## Périmètre critique — l'ordre est l'ordre de valeur

1. **Authentification** — page privée refusée à un invité ; connexion valide ; connexion invalide ; rate limiting.
2. **Autorisation** — un agent ne peut pas consulter ni modifier ce qui ne le concerne pas (403, pas 200 avec données d'autrui).
3. **Création d'appel** — payload valide → enregistré + redirection ; payload invalide → 422 avec les bons champs en erreur ; `user_id` forcé côté serveur et non pris depuis la requête.
4. **Filtres de liste** — chaque filtre (agent, statut, motif, période) restreint réellement le jeu de résultats ; combinaison de filtres ; filtre invalide rejeté.
5. **Rattachement à une réservation** — réservation existante acceptée, réservation inexistante rejetée, appel sans réservation accepté.
6. **Dashboard** — les agrégats renvoient les bons chiffres sur un jeu de données maîtrisé.

## Forme

```php
it('refuse un appel dont la durée est négative', function () {
    $agent = User::factory()->create();

    actingAs($agent)
        ->post(route('calls.store'), [...validCallPayload(), 'duration_seconds' => -1])
        ->assertSessionHasErrors('duration_seconds');

    expect(Call::count())->toBe(0);
});
```

- `RefreshDatabase` sur tout test touchant la base.
- Arrangement par factories et states nommés ; aucune dépendance à l'ordre d'exécution.
- Toujours tester le chemin refusé, pas seulement le chemin heureux.
- Dates de test figées avec `travelTo()` quand le test dépend du temps — sinon il casse un lundi matin.
- Assertions sur le comportement observable : statut HTTP, redirection, erreurs de session, état en base. Pas sur un appel de méthode interne.

## Ce qu'on ne teste pas

Les getters/setters, le framework lui-même, un accessor de formatage trivial, l'existence d'une route sans comportement. Un test sans risque couvert est un coût de maintenance sans contrepartie.

## Commandes

```bash
php artisan test                      # tout
php artisan test --filter=Call        # ciblé
php artisan test --parallel           # si la suite grossit
```

Une suite rouge n'est jamais « acceptable pour l'instant » : on corrige ou on retire le test devenu faux.
