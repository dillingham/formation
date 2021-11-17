<?php

namespace Dillingham\Formation;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;

class Manager
{
    protected string $routeName;

    protected array $resources = [];

    public function all()
    {
        return $this->resources;
    }

    public function create(array $resource)
    {
        $this->resources[] = $resource;
    }

    public function current()
    {
        $name = $this->getRouteName();

        foreach($this->resources as $resource) {
            foreach($resource['routes'] as $route) {
                if($route['key'] === $name) {
                    return $resource;
                }
            }
        }

        return null;
    }

    public function formation()
    {
        return app(Arr::get($this->current(), 'formation'));
    }

    public function getRouteName():string
    {
        return $this->routeName ?? Request::route()->getName();
    }

    public function setRouteName(string $name)
    {
        $this->routeName = $name;
    }
}
