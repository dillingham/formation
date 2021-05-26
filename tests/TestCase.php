<?php

namespace Dillingham\ListRequest\Tests;

use Dillingham\ListRequest\ListRequestProvider;
use Illuminate\Foundation\Auth\User;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            ListRequestProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        include_once __DIR__.'/Fixtures/Database/migrations/create_users_table.php.stub';
        include_once __DIR__.'/Fixtures/Database/migrations/create_posts_table.php.stub';
        include_once __DIR__.'/Fixtures/Database/migrations/create_likes_table.php.stub';
        include_once __DIR__.'/Fixtures/Database/migrations/create_comments_table.php.stub';

        (new \CreateUsersTable())->up();
        (new \CreatePostsTable())->up();
        (new \CreateLikesTable())->up();
        (new \CreateCommentsTable())->up();
    }

    public function authUser()
    {
        $user = User::forceCreate([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => '$2y$10$MTibKZXWRvtO2gWpfpsngOp6FQXWUhHPTF9flhsaPdWvRtsyMUlC2',
        ]);

        $this->actingAs($user);

        return $user;
    }
}
