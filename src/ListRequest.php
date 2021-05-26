<?php

namespace Dillingham\ListRequest;

use Dillingham\ListRequest\Exceptions\PageExceededException;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ListRequest extends FormRequest
{
    /**
     * Array of columns allowed to search by.
     *
     * @var array
     */
    protected $search = [];

    /**
     * Array of columns allowed to order by.
     *
     * @var array
     */
    protected $sort = ['created_at'];

    /**
     * Array of columns allowed to filter by.
     *
     * @var array
     */
    protected $filter = [];

    /**
     * The maximum number of items per page.
     *
     * @var int
     */
    protected $maxPerPage = 100;

    /**
     * The query builder instance.
     *
     * @var Builder
     */
    protected $builder;

    /**
     * The default parameters.
     *
     * @var mixed
     */
    protected $defaults = [];

    /**
     * The results.
     *
     * @var mixed
     */
    protected $results = [];

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

        $perPage = $this->input('per_page', $this->maxPerPage);

        if ($perPage > $this->maxPerPage) {
            $perPage = $this->maxPerPage;
        }

        $this->results = $this->getBuilderInstance()
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

        $query = $this->builder();
        $query = $this->applySort($query);
        $query = $this->applySearch($query);
        $query = $this->applyRanges($query);
        $query = $this->applyFilters($query);

        return $query;
    }

    /**
     * Get the query to add conditions to.
     *
     * @return Builder
     */
    public function builder()
    {
        throw new Exception('A ListRequest must have a builder() method', 500);
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
     * Apply ranges to the query.
     *
     * @var Builder
     * @return Builder
     */
    protected function applyRanges($query)
    {
        $columns = [];

        foreach ($this->ranges() as $range) {
            if ($this->request->get('range')
                && in_array($range->key, Arr::wrap($this->request->get('range')))) {
                if (in_array($range->column, $columns)) {
                    throw ValidationException::withMessages([
                        'range' => 'Ranges with same target are invalid.',
                    ]);
                }

                $query = $range->apply($query);
                $columns[] = $range->column;
            }
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
                $relation->getQualifiedForeignKeyName(), '=',
                $relation->getQualifiedParentKeyName()
            );
        } elseif (method_exists($query->getModel(), $sortable['column'])) {
            $query->withCount($sortable['column']);
            $sortable['column'] = $sortable['column'].'_count';
        }

        $query->orderBy($sortable['column'], $sortable['direction']);

        return $query;
    }

    public function getSortable()
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

    public function rules()
    {
        return [];
    }

    public function filters()
    {
        return [];
    }

    public function ranges()
    {
        return [];
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
