<?php

namespace Dillingham\Formation\Http\Controllers;

use Dillingham\Formation\Manager;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Controller extends BaseController
{
    public $manager;
    public $resource;
    public $parent;
    public $formation;
    public $routes;
    public $routeKeys;
    public $terms = [];

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(Manager $manager)
    {
        $current = $manager->current();

        $this->manager = $manager;
        $this->parent = $current['parent'];
        $this->resource = $current['resource'];
        $this->routes = $current['routes'];
        $this->routeKeys = $current['route_keys'];
        $this->formation = $current['formation'];
    }

    public function formation()
    {
        return app($this->formation);
    }

    public function model()
    {
        return app($this->formation()->model);
    }

    public function createRequest()
    {
        return app($this->formation()->create);
    }

    public function updateRequest()
    {
        return app($this->formation()->update);
    }

    public function resource()
    {
        return Request::route($this->routeKeys['resource']);
    }

    public function parent()
    {
        return Request::route($this->routeKeys['parent']);
    }

    public function route($key)
    {
        $route = collect($this->routes)->firstWhere('type', $key);

        return $route['key'];
    }

    public function transform($attributes)
    {
        $class = $this->formation()->resource;

        if(is_a($attributes, LengthAwarePaginator::class, true)) {
            return $class::collection($attributes);
        }
        return new $class($attributes);
    }

    public function response(string $type, $props = null): mixed
    {
        if($this->shouldRedirect($type)) {
            return $this->redirectResponse($type, $props);
        }

        if (Request::hasHeader('Wants-Json')) {
            return $this->apiResponse($type, $props);
        }

        if(config('formations.mode') === 'api') {
            return $this->apiResponse($type, $props);
        }

        if(config('formations.mode') === 'inertia') {
            return $this->inertiaResponse($type, $props);
        }

        return $this->bladeResponse($type, $props);
    }

    public function apiResponse($type, $props = null)
    {
        return $this->transform($props);
    }

    public function inertiaResponse($type, $props = null)
    {
        $term = null;

        if($type === 'index') {
            $term = 'resource.camelPlural';
        } else if(in_array($type, ['show','edit'])) {
            $term = 'resource.camel';
        }

        if($term) {
            $props = [
                $this->terms($term) => $this->transform($props)
            ];
        }

        $props = is_null($props) ? [] : $props;

        $view = $this->terms('resource.studlyPlural').'/'.ucfirst($type);

        return Inertia::render($view, $props);
    }

    public function bladeResponse($type, $props = null):string
    {
        $view = $this->terms('resource.slugPlural') .'.'. $type;

        return view($view)->with(
            $this->terms('resource.slugPlural').'.'.$type,
            $props
        );
    }

    public function redirectResponse($type, $props): RedirectResponse
    {
        if(in_array($type, ['store', 'update', 'restore'])) {
            return redirect()->route(
                $this->route('show'),
                $props->id
            );
        }

        return redirect()->route(
            $this->route('index'),
        );
    }


    public function shouldRedirect($type): bool
    {
        if(config('formations.mode') === 'api') {
            return false;
        }

        return in_array($type, [
            'store', 'update', 'restore',
            'destroy', 'force-delete'
        ]);
    }

    public function terms($key = null)
    {
        abort_if(!$this->resource, 500, 'Resource is required to use terms');

        if(empty($this->terms)) {
            $this->terms['resource'] = $this->getTerms($this->resource);
            $this->terms['parent'] = $this->getTerms($this->parent);
        }

        return Arr::get($this->terms, $key);
    }

    protected function getTerms($resource)
    {
        $resource = Str::of($resource)->replace('-', ' ')->lower();

        return [
            'lower' => (string) $resource,
            'lowerPlural' => (string) Str::of($resource)->plural(),
            'studly' => (string) Str::of($resource)->singular()->studly(),
            'studlyPlural' => (string) Str::of($resource)->plural()->studly(),
            'snake' => (string) Str::of($resource)->singular()->snake(),
            'snakePlural' => (string) Str::of($resource)->snake()->plural(),
            'slug' => (string) Str::of($resource)->singular()->slug(),
            'slugPlural' => (string) Str::of($resource)->slug()->plural(),
            'camel' => (string) Str::of($resource)->singular()->camel(),
            'camelPlural' => (string) Str::of($resource)->camel()->plural(),
        ];
    }
}
