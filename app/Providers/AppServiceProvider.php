<?php

namespace App\Providers;

use App\Models\MatchGame;
use App\Observers\MatchObserver;
use App\Services\FootballDataService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(FootballDataService::class, function ($app) {
            return new FootballDataService(config('services.football_data', []));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enregistrer l'observateur pour la qualification automatique
        MatchGame::observe(MatchObserver::class);

        // Configurer Carbon pour utiliser le français
        Carbon::setLocale('fr');
    }
}
