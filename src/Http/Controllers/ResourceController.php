<?php

namespace Dillingham\Formation\Http\Controllers;

use Dillingham\Formation\Manager;
use Illuminate\Support\Facades\Gate;

class ResourceController extends Controller
{
    public function __construct(Manager $resource)
    {
        parent::__construct($resource);

        if (Gate::getPolicyFor($this->formation()->model)) {
            $this->authorizeResource($this->formation()->model);
        }
    }

    public function index()
    {
        return $this->response(
            'index',
            $this->formation()->results()
        );
    }

    public function create()
    {
        return $this->response('create');
    }

    public function store()
    {
        $values = $this->createRequest()->validated();

        $model = $this->model()->create($values);

        return $this->response('store', $model);
    }

    public function show()
    {
        return $this->response('show', $this->resource());
    }

    public function edit()
    {
        return $this->response('edit', $this->resource());
    }

    public function update()
    {
        $values = $this->updateRequest()->validated();

        $this->resource()->update($values);

        return $this->response('update', $this->resource());
    }

    public function destroy()
    {
        $this->resource()->delete();

        return $this->response('destroy');
    }

    public function restore()
    {
        $model = $this->resource();

        $this->authorize('restore', $model);

        $model->restore();

        return $this->response('restore', $model);
    }

    public function forceDelete()
    {
        $model = $this->resource();

        $this->authorize('forceDelete', $model);

        $model->forceDelete();

        return $this->response('force-delete');
    }
}

