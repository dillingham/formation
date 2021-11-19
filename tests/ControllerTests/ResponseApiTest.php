<?php

namespace Dillingham\Formation\Tests\ControllerTests;

use Dillingham\Formation\Http\Controllers\Controller;
use Dillingham\Formation\Http\Resources\Resource;
use Dillingham\Formation\Manager;
use Dillingham\Formation\Tests\Fixtures\Models\Post;
use Dillingham\Formation\Tests\Fixtures\PostFormation;
use Dillingham\Formation\Tests\TestCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\View\View;

class ResponseApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('formations.mode', 'api');
    }

    public function test_index_api_responses()
    {
        Post::factory()->create(['title' => 'Hello World']);

        $response = $this
            ->getResourceController()
            ->response('index', Post::query()->paginate());

        $this->assertInstanceOf(AnonymousResourceCollection::class, $response);

        $this->assertEquals('Hello World', $response->resolve()[0]['title']);
    }

    public function test_create_api_responses()
    {
        $response = $this
            ->getResourceController()
            ->response('create');

        $this->assertInstanceOf(Resource::class, $response);
        $this->assertEquals('populated from extra method', $response->resolve()['extra']);
    }

    public function test_show_api_responses()
    {
        $post = Post::factory()->create();

        $response = $this
            ->getResourceController()
            ->response('show', $post);

        $this->assertInstanceOf(Resource::class, $response);
        $this->assertEquals($post->id, $response->resolve()['id']);
    }

    public function test_store_api_responses()
    {
        $post = Post::factory()->create();

        $response = $this
            ->getResourceController()
            ->response('store', $post);

        $this->assertInstanceOf(Resource::class, $response);
        $this->assertEquals($post->id, $response->resolve()['id']);
    }

    public function test_edit_api_responses()
    {
        $post = Post::factory()->create();

        $response = $this
            ->getResourceController()
            ->response('edit', $post);

        $this->assertInstanceOf(Resource::class, $response);
        $this->assertEquals($post->id, $response->resolve()['id']);
        $this->assertEquals('populated from override method', $response->resolve()['override']);
    }

    public function test_update_api_responses()
    {
        $post = Post::factory()->create();

        $response = $this
            ->getResourceController()
            ->response('update', $post);

        $this->assertInstanceOf(Resource::class, $response);
        $this->assertEquals($post->id, $response->resolve()['id']);
    }

    public function test_delete_api_responses()
    {
        $post = Post::factory()->create();

        $response = $this
            ->getResourceController()
            ->response('destroy', $post);

        $this->assertInstanceOf(Resource::class, $response);
        $this->assertEquals($post->id, $response->resolve()['id']);
    }

    public function test_restore_api_responses()
    {
        $post = Post::factory()->create();

        $response = $this
            ->getResourceController()
            ->response('restore', $post);

        $this->assertInstanceOf(Resource::class, $response);
        $this->assertEquals($post->id, $response->resolve()['id']);
    }

    public function test_force_delete_api_responses()
    {
        $post = Post::factory()->create();

        $response = $this
            ->getResourceController()
            ->response('force-delete', $post);

        $this->assertInstanceOf(Resource::class, $response);
        $this->assertEquals($post->id, $response->resolve()['id']);
    }
}
