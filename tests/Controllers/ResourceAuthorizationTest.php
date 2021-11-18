<?php

namespace Dillingham\Formation\Tests\Controllers;

use Dillingham\Formation\Tests\Fixtures\Models\Post;
use Dillingham\Formation\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResourceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authUser();
    }

    public function test_policy_for_indexing_a_resource()
    {
        config()->set('formations.testing-policies.viewAny', false);
        $this->get('posts')->assertForbidden();
        config()->set('formations.testing-policies.viewAny', true);
        $this->get('posts')->assertOk();
    }

    public function test_policy_for_creating_a_resource()
    {
        config()->set('formations.testing-policies.create', false);
        $this->get('posts/new')->assertForbidden();
        config()->set('formations.testing-policies.create', true);
        $this->get('posts/new')->assertOk();
    }

    public function test_policy_for_storing_a_resource()
    {
        config()->set('formations.testing-policies.create', false);
        $this->post('posts/new')->assertForbidden();
        config()->set('formations.testing-policies.create', true);
        $this->post('posts/new')->assertStatus(201);
    }

    public function test_policy_for_showing_a_resource()
    {
        $post = Post::factory()->create();
        config()->set('formations.testing-policies.view', false);
        $this->get("posts/$post->id")->assertForbidden();
        config()->set('formations.testing-policies.view', true);
        $this->get("posts/$post->id")->assertOk();
    }

    public function test_policy_for_editing_a_resource()
    {
        $post = Post::factory()->create();
        config()->set('formations.testing-policies.update', false);
        $this->get("posts/$post->id/edit")->assertForbidden();
        config()->set('formations.testing-policies.update', true);
        $this->get("posts/$post->id/edit")->assertOk();
    }

    public function test_policy_for_updating_a_resource()
    {
        $post = Post::factory()->create();
        config()->set('formations.testing-policies.update', false);
        $this->put("posts/$post->id/edit")->assertForbidden();
        config()->set('formations.testing-policies.update', true);
        $this->put("posts/$post->id/edit")->assertOk();
    }

    public function test_policy_for_deleting_a_resource()
    {
        $post = Post::factory()->create();
        config()->set('formations.testing-policies.delete', false);
        $this->delete("posts/$post->id")->assertForbidden();
        config()->set('formations.testing-policies.delete', true);
        $this->delete("posts/$post->id")->assertOk();
    }

    public function test_policy_for_restoring_a_resource()
    {
        $post = Post::factory()->create();
        config()->set('formations.testing-policies.restore', false);
        $this->post("posts/$post->id/restore")->assertForbidden();
        config()->set('formations.testing-policies.restore', true);
        $this->post("posts/$post->id/restore")->assertOk();
    }

    public function test_policy_for_force_deleting_a_resource()
    {
        $post = Post::factory()->create();
        config()->set('formations.testing-policies.forceDelete', false);
        $this->delete("posts/$post->id/force-delete")->assertForbidden();
        config()->set('formations.testing-policies.forceDelete', true);
        $this->delete("posts/$post->id/force-delete")->assertOk();
    }
}
