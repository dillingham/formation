<?php

namespace Dillingham\Formation\Tests\Controllers;

use Dillingham\Formation\Tests\Fixtures\Models\Post;
use Dillingham\Formation\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResourceValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authUser();
    }

    public function test_validate_storing_a_resource()
    {
        $this->markTestIncomplete();
    }

    public function test_validate_updating_a_resource()
    {
        $this->markTestIncomplete();
    }
}
