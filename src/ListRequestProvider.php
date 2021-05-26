<?php

namespace Dillingham\ListRequest;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Console\Input\InputOption;

class ListRequestProvider extends ServiceProvider
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
                ListRequestMakeCommand::class,
            ]);
        }

        Event::listen(CommandStarting::class, function ($event) {
            if ($event->command != 'make:request') {
                return;
            }

            $commands = Artisan::all();

            $definition = $commands[$event->command]->getDefinition();

            $definition->addOption(new InputOption('list', null, 1));
        });

        Event::listen(CommandFinished::class, function ($event) {
            if ($event->command != 'make:request' || ! $event->input->getOptions()['list']) {
                return;
            }

            $name = $event->input->getArguments()['name'];

            Artisan::call("make:list-request ${name} --force");
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
