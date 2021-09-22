<?php

namespace Dillingham\Formation\Tests;

use Dillingham\Formation\FormationProvider;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected $useMysql = false;

    protected function getPackageProviders($app)
    {
        return [
            FormationProvider::class,
        ];
    }

    public function useMysql()
    {
        $this->useMysql = true;
    }

    public function getEnvironmentSetUp($app)
    {
        if(! $this->useMysql) {
            $app['config']->set('database.default', 'sqlite');
            $app['config']->set('database.connections.sqlite', [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]);
        }

        include_once __DIR__.'/Fixtures/Database/migrations/create_users_table.php.stub';
        include_once __DIR__.'/Fixtures/Database/migrations/create_posts_table.php.stub';
        include_once __DIR__.'/Fixtures/Database/migrations/create_likes_table.php.stub';
        include_once __DIR__.'/Fixtures/Database/migrations/create_comments_table.php.stub';
        include_once __DIR__.'/Fixtures/Database/migrations/create_tags_table.php.stub';

        Schema::dropIfExists('users');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('likes');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('post_tag');

        (new \CreateUsersTable())->up();
        (new \CreatePostsTable())->up();
        (new \CreateLikesTable())->up();
        (new \CreateCommentsTable())->up();
        (new \CreateTagsTable())->up();
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
