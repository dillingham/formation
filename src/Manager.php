<?php

namespace Dillingham\Formation;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;

class Manager
{
    /**
     * The current resource.
     *
     * @var
     */
    protected $current;

    /**
     * The resources.
     *
     * @var array
     */
    protected $resources = [];

    /**
     * Retrieve all resources.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->resources;
    }

    /**
     * Register a new resource.
     *
     * @param array $resource
     */
    public function register(array $resource)
    {
        $this->resources[] = $resource;
    }

    /**
     * The current formation object.
     *
     * @return Formation
     */
    public function formation(): Formation
    {
        return app(Arr::get($this->current(), 'formation'));
    }

    /**
     * The current resource settings.
     *
     * @return mixed|null
     */
    public function current()
    {
        if($this->current) {
            return $this->current;
        }

        $name = Request::route()->getName();

        foreach ($this->resources as $resource) {
            foreach ($resource['routes'] as $route) {
                if ($route['key'] === $name) {
                    $this->current = $resource;
                    return $resource;
                }
            }
        }

        return null;
    }
}
