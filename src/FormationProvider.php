<?php

namespace Dillingham\Formation;

use Dillingham\Formation\Commands\FormationMakeCommand;
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

        Route::macro('formation', function ($resource, $formation, array $routes = []) {
            $routes = (new Routing($resource, $formation, $routes, $this->getLastGroupPrefix()))->create($this);
            $resourceRouteKey = (string) Str::of($resource)->replace('-', '_')->singular();

            app(Manager::class)->create([
                'formation' => $formation,
                'resource' => $resource,
                'routes' => $routes,
                'resource_route_key' => $resourceRouteKey,
            ]);
        });
    }
}
