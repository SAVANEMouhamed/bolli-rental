<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
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
    }
}
