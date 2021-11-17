<?php

namespace Dillingham\Formation;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Console\Input\InputOption;

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
        $this->mergeConfigFrom( __DIR__.'/../config/formations.php', 'formations');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/formations.php' => config_path('formations.php')
        ], 'formations');
    }
}
