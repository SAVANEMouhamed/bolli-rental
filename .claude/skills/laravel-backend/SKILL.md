---
name: laravel-backend
description: Conventions backend Laravel du projet Bolli Rental — contrôleurs minces, Form Requests, Policies, enums, services justifiés, gestion d'erreurs.
when_to_use: Avant d'écrire ou de modifier un contrôleur, une route, une Form Request, une Policy, un enum, un service, un job ou un middleware. Aussi quand on se demande où placer une logique métier.
paths:
    - 'app/**/*.php'
    - 'routes/**/*.php'
---

## Où va quoi

| Besoin                                | Emplacement                                                             |
| ------------------------------------- | ----------------------------------------------------------------------- |
| Contraindre la forme de l'entrée      | Form Request                                                            |
| Décider si l'utilisateur a le droit   | Policy (+ `authorize()` ou middleware `can:`)                           |
| Filtrer/trier/paginer une liste       | scope Eloquent ou query object appelé par le contrôleur                 |
| Règle métier réutilisée à 2+ endroits | méthode de modèle, ou Service si elle dépasse le modèle                 |
| Traitement long ou externe            | Job (uniquement quand réellement nécessaire)                            |
| Réaction découplée à un fait métier   | Event/Listener (uniquement si plusieurs réactions ou couplage à casser) |
| Forme de la donnée envoyée à Vue      | tableau construit dans le contrôleur, ou Resource si réutilisée         |

## Squelette d'un contrôleur ressource

```php
final class CallController extends Controller
{
    public function index(IndexCallRequest $request): Response
    {
        $this->authorize('viewAny', Call::class);

        return Inertia::render('calls/Index', [
            'calls'   => Call::query()
                ->filter($request->validated())
                ->with(['client:id,full_name', 'user:id,name', 'reservation:id,vehicle'])
                ->latest('called_at')
                ->paginate(20)
                ->withQueryString(),
            'filters' => $request->validated(),
        ]);
    }

    public function store(StoreCallRequest $request): RedirectResponse
    {
        $call = Call::create($request->validated() + ['user_id' => $request->user()->id]);

        return to_route('calls.show', $call)->with('success', 'Appel enregistré.');
    }
}
```

Points à répliquer : `final`, types de retour, `validated()` jamais `all()`, autorisation explicite, eager loading avec colonnes ciblées, `withQueryString()` pour conserver les filtres, redirection nommée + message flash.

## Form Requests

- `authorize()` renvoie une décision réelle, pas `true` par défaut.
- `rules()` valide type, format, longueur, valeurs autorisées (`Rule::enum(...)`), existence des relations (`exists:clients,id`), et cohérence métier (`after_or_equal`, règle dédiée).
- `messages()` uniquement si le message par défaut est incompréhensible pour un agent du service client.
- Les filtres de liste passent aussi par une Form Request : une valeur de filtre est une entrée utilisateur.

## Enums

Un enum PHP backé par une chaîne pour `direction`, `reason`, `status`. Exposer une méthode `label()` pour l'affichage et `values()` pour la validation. Caster dans le modèle. Aucune chaîne magique ailleurs.

## Erreurs

- Exceptions métier typées, converties en réponse via `render()` ou un handler.
- Aucun message technique ni trace renvoyés en production.
- `abort(403)` et `abort(404)` plutôt qu'un booléen silencieux.

## Interdits

`$request->all()` en écriture · logique métier dans `routes/` · `env()` hors `config/` · façade dans une classe métier testable · contrôleur qui construit lui-même une requête SQL complexe ligne à ligne · service créé pour « faire propre ».
