<?php

namespace Dillingham\Formation\Tests\Fixtures;

use Dillingham\Formation\Tests\Fixtures\Models\Post;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class TestProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Gate::policy(Post::class, PostPolicy::class);

        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}
