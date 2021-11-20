<?php

namespace Dillingham\Formation;

use Dillingham\Formation\Http\Requests\CreateRequest;
use Dillingham\Formation\Http\Resources\Resource;
use Dillingham\Formation\Http\Requests\UpdateRequest;
use Dillingham\Formation\Exceptions\PageExceededException;
use Dillingham\Formation\Http\Controllers\ResourceController;
use Dillingham\Formation\Scopes\SearchScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class Formation extends FormRequest
{
    use Concerns\HasData;
    use Concerns\HasQueries;

    /**
     * The model instance.
     *
     * @var Model
     */
    public $model;

    /**
     * The resource controller.
     *
     * @var string
     */
    public $controller = ResourceController::class;

    /**
     * The default create request.
     *
     * @var string
     */
    public $create = CreateRequest::class;

    /**
     * The default update request.
     *
     * @var string
     */
    public $update = UpdateRequest::class;

    /**
     * The default api resource.
     *
     * @var string
     */
    public $resource = Resource::class;

    /**
     * The select option display column.
     *
     * @var array
     */
    public $display = 'id';

    /**
     * Array of columns allowed to search by.
     *
     * @var array
     */
    public $search = [];

    /**
     * Array of columns allowed to order by.
     *
     * @var array
     */
    public $sort = ['created_at'];

    /**
     * The maximum number of items per page.
     *
     * @var int
     */
    public $maxPerPage = 100;

    /**
     * The default parameters.
     *
     * @var mixed
     */
    public $defaults = [];

    /**
     * The select overrides.
     *
     * @var mixed
     */
    public $select = [];

    /**
     * The results.
     *
     * @var mixed
     */
    protected $results = [];

    /**
     * The conditions.
     *
     * @var array
     */
    protected $conditions = [];

    /**
     * If request was called.
     *
     * @var bool
     */
    protected $wasRequested = false;

    /**
     * Build the query upon method injection.
     */
    public function prepareForValidation()
    {
        $this->getValidatorInstance()
            ->addRules($this->getInternalRules());
    }

    /**
     * Perform the query.
     *
     * @return Collection
     */
    public function results()
    {
        if ($this->wasRequested) {
            return $this->results;
        }

        $builder = $this->getBuilderInstance();

        $perPage = $this->input('per_page', $builder->getModel()->getPerPage());

        if ($perPage > $this->maxPerPage) {
            $perPage = $this->maxPerPage;
        }

        $this->results = $builder
            ->paginate($perPage)
            ->withQueryString();

        $this->validatePagination($this->results);

        $this->wasRequested = true;

        return $this->results;
    }

    /**
     * Get validation rules for url parameters.
     *
     * @var array
     */
    protected function getInternalRules():array
    {
        $rules = [
            'search' => 'nullable|string|min:1|max:64',
            'per_page' => "nullable|integer|min:1,max:{$this->maxPerPage}",
            'sort' => 'nullable|string|in:'.$this->getSortableKeys(),
            'sort-desc' => 'nullable|string|in:'.$this->getSortableKeys(),
        ];

        foreach ($this->filters() as $filter) {
            $filter->setRequest($this);
            foreach ($filter->getRules() as $key => $rule) {
                $rules[$key] = $rule;
            }
        }

        return $rules;
    }

    /**
     * Perform the query.
     *
     * @return Builder
     */
    private function getBuilderInstance()
    {
        $this->applyDefaults();

        $query = app($this->model)->query();
        $this->indexQuery($query);
        $query = $this->applySort($query);
        $query = $this->applySearch($query);
        $query = $this->applySelect($query);
        $query = $this->applyFilters($query);
        $query = $this->applyConditions($query);

        return $query;
    }

    /**
     * Apply defaults to the request.
     *
     * @var Builder
     * @return $this
     */
    protected function applyDefaults()
    {
        foreach ($this->defaults as $key => $value) {
            if (! $this->has($key)) {
                $this->merge([$key => $value]);
            }
        }

        return $this;
    }

    /**
     * Apply search to the query.
     *
     * @var Builder
     * @return Builder
     */
    protected function applySearch($query)
    {
        if ($term = $this->input('search')) {
            $query = (new SearchScope())->apply($query, $this->search, $term);
        }

        return $query;
    }

    /**
     * Apply filters to the query.
     *
     * @var Builder
     * @return Builder
     */
    protected function applyFilters($query)
    {
        foreach ($this->filters() as $filter) {
            $filter->setRequest($this);
            $filter->apply($query);
        }

        return $query;
    }

    /**
     * Apply conditions to the query.
     *
     * @var Builder
     * @return Builder
     */
    protected function applyConditions($query)
    {
        return $query->where($this->conditions);
    }

    /**
     * Apply selects to the query.
     *
     * @var Builder
     * @return Builder
     */
    protected function applySelect($query)
    {
        if(count($this->select)) {
            return $query->select($this->select);
        }

        return $query;
    }

    /**
     * Apply sort to the query.
     *
     * @var Builder
     * @return Builder
     */
    protected function applySort($query)
    {
        $sortable = $this->getSortable();

        if (empty($sortable)) {
            return $query;
        }

        if (! empty($sortable['relationship'])) {
            $relation = $query->getModel()->{$sortable['relationship']}();

            $query->addSelect([
                $relation->getModel()->getTable().'.'.$relation->getLocalKeyName(), // comments.id
                $relation->getQualifiedForeignKeyName(), // comments.post_id
                $relation->getModel()->getTable().'.'.$sortable['column'], // comments.upvotes
            ]);

            $query->join(
                $relation->getModel()->getTable(),
                $relation->getQualifiedForeignKeyName(),
                '=',
                $relation->getQualifiedParentKeyName()
            );
        } elseif (method_exists($query->getModel(), $sortable['column'])) {
            $query->withCount($sortable['column']);
            $sortable['column'] = $sortable['column'].'_count';
        }

        $query->orderBy($sortable['column'], $sortable['direction']);

        return $query;
    }

    public function getSortable(): array
    {
        $sortable = [
            'relationship' => null,
            'alias' => null,
        ];

        if ($this->filled('sort')) {
            $sortable = [
                'column' => $this->input('sort'),
                'direction'=> 'asc',
            ];
        } elseif ($this->filled('sort-desc')) {
            $sortable = [
                'column' => $this->input('sort-desc'),
                'direction'=> 'desc',
            ];
        }

        if (! isset($sortable['column'])) {
            return [];
        }

        foreach ($this->sort as $definition) {
            if (Str::endsWith($definition, '.'.$sortable['column'])) {
                $sortable['column'] = Str::after($definition, '.');
                $sortable['relationship'] = Str::before($definition, '.');
            } elseif (Str::endsWith($definition, ' as '.$sortable['column'])) {
                $sortable['alias'] = $sortable['column'];
                $sortable['column'] = Str::before($definition, ' as '.$sortable['column']);
                if (Str::contains($sortable['column'], '.')) {
                    $sortable['relationship'] = Str::before($sortable['column'], '.');
                    $sortable['column'] = Str::after($sortable['column'], '.');
                }
            }
        }

        return $sortable;
    }

    public function validatePagination($results)
    {
        if (request()->input('page') > $results->lastPage()) {
            throw new PageExceededException();
        }
    }

    public function rules(): array
    {
        return [];
    }

    public function rulesForIndexing(): array
    {
        return [];
    }

    public function rulesForCreating(): array
    {
        return [];
    }

    public function rulesForUpdating(): array
    {
        return [];
    }


    public function filters(): array
    {
        return [];
    }

    public function where($key, $value): Formation
    {
        $this->conditions[$key] = $value;

        return $this;
    }

    public function select(array $select): Formation
    {
        $this->select = $select;

        return $this;
    }

    public function options(): Formation
    {
        return $this->select([
            $this->display . ' as display',
            app($this->model)->getKeyName() . ' as value',
        ]);
    }

    public function getSortableKeys()
    {
        $keys = [];

        foreach ($this->sort as $sort) {
            if (Str::contains($sort, ' as ')) {
                $bits = explode(' as ', $sort);
                $keys[] = $bits[1];
            } else {
                if (Str::contains($sort, '.')) {
                    $bits = explode('.', $sort);
                    $keys[] = $bits[1];
                } else {
                    $keys[] = $sort;
                }
            }
        }

        return implode(',', $keys);
    }
}
