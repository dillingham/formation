<?php

namespace Dillingham\Formation\Http\Controllers;

class ResourceController extends Controller
{
    public function index()
    {
        $this->check('viewAny', $this->model());

        return $this->response(
            'index',
            $this->formation()->results()
        );
    }

    public function create()
    {
        $this->check('create', $this->model());

        return $this->response('create');
    }

    public function store()
    {
        $this->check('create', $this->model());

        $values = $this->createRequest()->validated();

        $resource = $this->model()->create($values);

        return $this->response('store', $resource);
    }

    public function show()
    {
        $resource = $this->resource();

        $this->check('view', $resource);

        return $this->response('show', $resource);
    }

    public function edit()
    {
        $resource = $this->resource();

        $this->check('update', $resource);

        return $this->response('edit', $resource);
    }

    public function update()
    {
        $resource = $this->resource();

        $this->check('update', $resource);

        $values = $this->updateRequest()->validated();

        $resource->update($values);

        return $this->response('update', $resource);
    }

    public function destroy()
    {
        $resource = $this->resource();

        $this->check('delete', $resource);

        $resource->delete();

        return $this->response('destroy');
    }

    public function restore()
    {
        $resource = $this->resource();

        $this->check('restore', $resource);

        $resource->restore();

        return $this->response('restore', $resource);
    }

    public function forceDelete()
    {
        $resource = $this->resource();

        $this->check('forceDelete', $resource);

        $resource->forceDelete();

        return $this->response('force-delete');
    }
}
