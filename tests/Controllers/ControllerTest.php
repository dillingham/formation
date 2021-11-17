<?php

namespace Dillingham\Formation\Tests\Controllers;

use Dillingham\Formation\Http\Controllers\Controller;
use Dillingham\Formation\Manager;
use Dillingham\Formation\Tests\Fixtures\Models\Post;
use Dillingham\Formation\Tests\Fixtures\PostFormation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
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

    public function test_controller_terms()
    {
        app(Manager::class)->setRouteName('posts.index');

        $controller = app(Controller::class);
        $controller->parent = 'brand';
        $controller->resource = 'product-lines';

        $this->assertEquals('ProductLine', $controller->terms('resource.studly'));
        $this->assertEquals('ProductLines', $controller->terms('resource.studlyPlural'));
        $this->assertEquals('product_line', $controller->terms('resource.snake'));
        $this->assertEquals('product_lines', $controller->terms('resource.snakePlural'));
        $this->assertEquals('product-line', $controller->terms('resource.slug'));
        $this->assertEquals('product-lines', $controller->terms('resource.slugPlural'));
        $this->assertEquals('productLine', $controller->terms('resource.camel'));
        $this->assertEquals('productLines', $controller->terms('resource.camelPlural'));
        $this->assertEquals('Brand', $controller->terms('parent.studly'));
        $this->assertEquals('Brands', $controller->terms('parent.studlyPlural'));
        $this->assertEquals('brand', $controller->terms('parent.snake'));
        $this->assertEquals('brands', $controller->terms('parent.snakePlural'));
        $this->assertEquals('brand', $controller->terms('parent.slug'));
        $this->assertEquals('brands', $controller->terms('parent.slugPlural'));
        $this->assertEquals('brand', $controller->terms('parent.camel'));
        $this->assertEquals('brands', $controller->terms('parent.camelPlural'));
    }
}
