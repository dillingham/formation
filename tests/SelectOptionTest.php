<?php

namespace Dillingham\Formation\Tests;

use Dillingham\Formation\Http\Controllers\SelectOptionController;
use Dillingham\Formation\Tests\Fixtures\PostFormation;
use Dillingham\Formation\Tests\Fixtures\Post;
use Illuminate\Support\Facades\Route;

class SelectOptionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config()->set('formations.options', [
            'posts' => PostFormation::class,
        ]);

        Route::get('/options/{resource}', SelectOptionController::class);
    }

    public function test_filtering_options_with_search()
    {
        Post::factory()->create(['title' => 'bad']);

        $good = Post::factory()->create(['title' => 'good']);

        $this->get('options/posts')
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('current_page', 1)
            ->assertJsonPath('total', 2);

        $this->get('options/posts?search=good')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.display', 'good')
            ->assertJsonPath('data.0.value', (string) $good->id);
    }
}
