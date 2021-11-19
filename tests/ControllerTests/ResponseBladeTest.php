<?php

namespace Dillingham\Formation\Tests\ControllerTests;

use Dillingham\Formation\Tests\Fixtures\Models\Post;
use Dillingham\Formation\Tests\TestCase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ResponseBladeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('formations.mode', 'blade');
    }

    public function test_index_blade_responses()
    {
        Post::factory()->create(['title' => 'Hello World']);

        $index = $this->getResourceController()
            ->response('index', Post::query()->paginate());

        $this->assertInstanceOf(View::class, $index);
        $this->assertEquals('testing::posts.index', $index->name());
        $this->assertArrayHasKey('posts', $index->getData());
        $this->assertInstanceOf(LengthAwarePaginator::class, $index->getData()['posts']);
        $this->assertEquals('Hello World', $index->getData()['posts']->first()->title);
    }

    public function test_create_blade_responses()
    {
        $create = $this
            ->getResourceController()
            ->response('create');

        $this->assertInstanceOf(View::class, $create);
        $this->assertEquals('testing::posts.create', $create->name());
        $this->assertEquals('populated from extra method', $create->getData()['extra']);
    }

    public function test_show_blade_responses()
    {
        $post = Post::factory()->create(['title' => 'Hello World']);

        $show = $this
            ->getResourceController()
            ->response('show', $post);

        $this->assertInstanceOf(View::class, $show);
        $this->assertEquals('testing::posts.show', $show->name());
        $this->assertArrayHasKey('post', $show->getData());
        $this->assertEquals('Hello World', $show->getData()['post']->title);
    }

    public function test_store_blade_responses()
    {
        $post = Post::factory()->create();

        $store = $this
            ->getResourceController()
            ->response('store', $post);

        $this->assertInstanceOf(RedirectResponse::class, $store);
        $this->assertEquals(url(route('posts.show', $post)), $store->getTargetUrl());
    }

    public function test_edit_blade_responses()
    {
        $post = Post::factory()->create(['title' => 'Hello World']);

        $edit = $this
            ->getResourceController()
            ->response('edit', $post);

        $this->assertInstanceOf(View::class, $edit);
        $this->assertEquals('testing::posts.edit', $edit->name());
        $this->assertArrayHasKey('id', $edit->getData());
        $this->assertEquals($post->id, $edit->getData()['id']);
        $this->assertEquals('populated from override method', $edit->getData()['override']);
    }

    public function test_update_blade_responses()
    {
        $post = Post::factory()->create();

        $update = $this
            ->getResourceController()
            ->response('update', $post);

        $this->assertInstanceOf(RedirectResponse::class, $update);
        $this->assertEquals(url(route('posts.show', $post)), $update->getTargetUrl());
    }

    public function test_destroy_blade_responses()
    {
        $post = Post::factory()->create();

        $destroy = $this
            ->getResourceController()
            ->response('destroy', $post);

        $this->assertInstanceOf(RedirectResponse::class, $destroy);
        $this->assertEquals(url(route('posts.index')), $destroy->getTargetUrl());
    }

    public function test_restore_blade_responses()
    {
        $post = Post::factory()->create();

        $restore = $this
            ->getResourceController()
            ->response('restore', $post);

        $this->assertInstanceOf(RedirectResponse::class, $restore);
        $this->assertEquals(url(route('posts.show', $post)), $restore->getTargetUrl());
    }

    public function test_force_delete_blade_responses()
    {
        $post = Post::factory()->create();

        $forceDelete = $this
            ->getResourceController()
            ->response('force-delete', $post);

        $this->assertInstanceOf(RedirectResponse::class, $forceDelete);
        $this->assertEquals(url(route('posts.index')), $forceDelete->getTargetUrl());
    }
}
