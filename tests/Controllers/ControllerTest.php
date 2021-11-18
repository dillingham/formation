<?php

namespace Dillingham\Formation\Tests\Controllers;

use Dillingham\Formation\Http\Controllers\Controller;
use Dillingham\Formation\Manager;
use Dillingham\Formation\Tests\Fixtures\PostFormation;
use Dillingham\Formation\Tests\TestCase;

class ControllerTest extends TestCase
{
    public function test_resource_singleton()
    {
        $resources = app(Manager::class)->all();

        $this->assertCount(1, $resources);
        $this->assertEquals('posts', $resources[0]['resource']);
        $this->assertEquals(PostFormation::class, $resources[0]['formation']);
    }

    public function test_resource_terms()
    {
        $controller = app(Controller::class);
        $controller->current['resource'] = 'product-lines';

        $this->assertEquals('ProductLine', $controller->terms('resource.studly'));
        $this->assertEquals('ProductLines', $controller->terms('resource.studlyPlural'));
        $this->assertEquals('product_line', $controller->terms('resource.snake'));
        $this->assertEquals('product_lines', $controller->terms('resource.snakePlural'));
        $this->assertEquals('product-line', $controller->terms('resource.slug'));
        $this->assertEquals('product-lines', $controller->terms('resource.slugPlural'));
        $this->assertEquals('productLine', $controller->terms('resource.camel'));
        $this->assertEquals('productLines', $controller->terms('resource.camelPlural'));
    }

    public function test_redirect_responses()
    {
        $this->markTestIncomplete();
    }

    public function test_api_responses()
    {
        $this->markTestIncomplete();
    }

    public function test_blade_responses()
    {
        $this->markTestIncomplete();
    }

    public function test_inertia_responses()
    {
        $this->markTestIncomplete();
    }
}
