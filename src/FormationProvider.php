<?php

namespace Dillingham\Formation;

use Dillingham\Formation\Commands\FormationMakeCommand;
use Dillingham\Formation\Http\Controllers\ResourceController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class FormationProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                FormationMakeCommand::class,
            ]);
        }

        $this->app->singleton(Manager::class, function () {
            return new Manager();
        });

        $this->mergeConfigFrom(__DIR__.'/../config/formations.php', 'formations');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/formations.php' => config_path('formations.php'),
        ], 'formations');

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'formations');

        Route::macro('formation', function ($resource, $formation, array $routes = []) {

            return app(Routing::class)
                ->setResource($resource)
                ->setFormation($formation)
                ->setRoutes($routes)
                ->create();
        });
    }
}
