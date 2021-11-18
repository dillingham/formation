<?php

namespace Dillingham\Formation\Tests\Controllers;

use Dillingham\Formation\Tests\Fixtures\Models\Post;
use Dillingham\Formation\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authUser();
    }

    public function test_indexing_a_resource()
    {
        $post = Post::factory()->create();

        $this->get('posts')
            ->assertOk()
            ->assertJsonPath('data.0.id', $post->id);
    }

    public function test_searching_a_resource_index()
    {
        Post::factory()->create();

        $post = Post::factory()->create(['title' => 'Find me']);

        $this->get('posts?search=find')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $post->id);
    }

    public function test_creating_a_resource()
    {
        $this->get('posts/new')->assertOk();
    }

    public function test_storing_a_resource()
    {
        $this->post('posts/new', [
            'title' => 'Blog title',
        ]);

        $this->assertEquals('Blog title', Post::first()->title);
    }

    public function test_showing_a_resource()
    {
        $post = Post::factory()->create();

        $this->get("posts/$post->id")
            ->assertOk()
            ->assertJsonPath('data.id', $post->id);
    }

    public function test_showing_a_deleted_resource()
    {
        $post = Post::factory()->create();

        $post->delete();

        $this->get("posts/$post->id")
            ->assertOk()
            ->assertJsonPath('data.id', $post->id);
    }

    public function test_editing_a_resource()
    {
        $post = Post::factory()->create();

        $this->get("posts/$post->id/edit")
            ->assertOk()
            ->assertJsonPath('data.id', $post->id);
    }

    public function test_updating_a_resource()
    {
        $post = Post::factory()->create();

        $this->put("posts/$post->id/edit", [
            'title' => 'new title',
        ])->assertOk();

        $this->assertEquals('new title', $post->fresh()->title);
    }

    public function test_deleting_a_resource()
    {
        $post = Post::factory()->create();

        $this->delete("posts/$post->id")->assertOk();

        $this->assertCount(0, Post::all());
        $this->assertCount(1, Post::withTrashed()->get());
    }

    public function test_restoring_a_resource()
    {
        $post = Post::factory()->create();

        $post->delete();

        $this->post("posts/$post->id/restore")->assertOk();

        $this->assertCount(1, Post::all());
    }

    public function test_force_deleting_a_resource()
    {
        $post = Post::factory()->create();

        $post->delete();

        $this->delete("/posts/$post->id/force-delete")->assertOk();

        $this->assertEquals(0, Post::withTrashed()->count());
    }
}
