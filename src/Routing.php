<?php

namespace Dillingham\Formation;

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class Routing
{
    protected $resource;

    protected $routes = [];

    protected $formation;

    protected $prefix;

    protected $routeTypes = [
        'index' => ['GET', 'HEAD'],
        'create' => ['GET', 'HEAD'],
        'show' => ['GET', 'HEAD'],
        'store' => 'POST',
        'edit' => ['GET', 'HEAD'],
        'update' => 'PUT',
        'destroy' => 'DELETE',
        'restore' => 'POST',
        'force-delete' => 'DELETE',
    ];

    public function __construct(string $resource, string $formation, array $routes = [], $prefix = null)
    {
        $this->resource = $resource;
        $this->formation = $formation;
        $this->routes = $routes;
        $this->prefix = $prefix;
    }

    public function make()
    {
        $routeTypes = empty($this->routes)
            ? $this->routeTypes
            : Arr::only($this->routeTypes, $this->routes);

        $endpoints = $this->endpoints($this->resource);

        $output = [];

        foreach ($routeTypes as $name => $verb) {
            $output[] = [
                'verb' => $verb,
                'endpoint' => $endpoints[$name], // brands.product-line is currently wrong for nested / parent
                'action' => [app($this->formation)->controller, Str::camel($name)],
                'name' => "$this->resource.$name",
                'key' => $this->prefix ? "$this->prefix.$this->resource.$name" : "$this->resource.$name",
                'type' => $name,
                'with-trashed' => in_array($name, ['show', 'restore', 'force-delete']),
            ];
        }

        return $output;
    }

    public function create(Router $router):array
    {
        $routes = $this->make();

        foreach ($routes as $route) {
            $router
                ->addRoute($route['verb'], $route['endpoint'], $route['action'])
                ->name($route['name'])
                ->withTrashed($route['with-trashed']);
        }

        return $routes;
    }

    private function endpoints(string $resource): array
    {
        $singular = (string) Str::of($resource)->replace('-', '_')->singular();

        $endpoints = [
            'index' => $resource,
            'create' => "$resource/new",
            'store' => "$resource/new",
            'show' => "$resource/{{$singular}}",
            'edit' => "$resource/{{$singular}}/edit",
            'update' => "$resource/{{$singular}}/edit",
            'destroy' => "$resource/{{$singular}}",
            'restore' => "$resource/{{$singular}}/restore",
            'force-delete' => "$resource/{{$singular}}/force-delete",
        ];

        return $endpoints;
    }
}
