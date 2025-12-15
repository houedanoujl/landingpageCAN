<?php

namespace App\Providers;

use App\Models\MatchGame;
use App\Observers\MatchObserver;
use Illuminate\Support\ServiceProvider;

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
        // Enregistrer l'observateur pour la qualification automatique
        MatchGame::observe(MatchObserver::class);
    }
}
