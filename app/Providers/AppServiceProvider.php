<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        // Une resource enveloppée dans `data` casse les props Inertia : le composant
        // Vue reçoit `{data: [...]}` là où il attend une liste, et la page tombe en
        // blanc. Règle unique pour toute l'application : l'enveloppe `data` n'existe
        // que là où la pagination l'impose, c'est-à-dire dans les collections
        // paginées, qui doivent loger `links` et `meta` à côté des lignes.
        JsonResource::withoutWrapping();

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        // Outil interne manipulant des données clients : la même exigence de mot de passe
        // s'applique en local et en production, sinon le compte de démo est plus faible
        // que ce que le déploiement impose.
        Password::defaults(function (): Password {
            $rule = Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols();

            // uncompromised() interroge l'API Have I Been Pwned : la suite de
            // tests ne doit pas dépendre d'un appel réseau externe.
            return app()->runningUnitTests() ? $rule : $rule->uncompromised();
        });

        // Un N+1 doit échouer pendant le développement, pas ralentir la production.
        Model::preventLazyLoading(! app()->isProduction());

        // Même logique pour une colonne absente d'un `select()` ciblé : sans ce
        // garde, l'attribut vaut silencieusement null et la panne n'apparaît que
        // plus loin, au formatage, sous la forme d'une erreur incompréhensible.
        Model::preventAccessingMissingAttributes(! app()->isProduction());
    }
}
