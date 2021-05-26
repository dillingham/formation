<?php

namespace Dillingham\ListRequest;

use Dillingham\ListRequest\Exceptions\ReservedException;
use Dillingham\ListRequest\Exceptions\UnauthorizedException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class Filter
{
    /**
     * The query string key.
     *
     * @var
     */
    public $publicKey;

    /**
     * The internal key.
     *
     * @var
     */
    protected $key;

    /**
     * The validation rules.
     *
     * @var array
     */
    public $rules = [];

    /**
     * The query callbacks.
     *
     * @var array
     */
    public $queries = [];

    /**
     * The conditionals query callbacks.
     *
     * @var array
     */
    protected $conditionals = [];

    /**
     * The query parameter suffixes.
     *
     * @var array
     */
    protected $modifiers = [];

    /**
     * The active modifier(s).
     *
     * @var
     */
    protected $modifier;

    /**
     * The active value(s).
     *
     * @var
     */
    protected $value;

    /**
     * The active query.
     *
     * @var
     */
    protected $query;

    /**
     * The active request.
     *
     * @var
     */
    protected $request;

    /**
     * The query requires auth.
     *
     * @var
     */
    protected $authenticated = false;

    /**
     * The query handles multiple.
     *
     * @var
     */
    public $multiple = false;

    /**
     * The query method was called.
     *
     * @var
     */
    public $filterMethodCalled = false;

    /**
     * Make a filter instance.
     *
     * @param $key
     * @return Filter
     */
    public static function make($key)
    {
        if (in_array($key, ['search', 'per_page', 'sort', 'sort-desc'])) {
            throw new ReservedException();
        }

        return (new self)->key($key);
    }

    /**
     * Add the filter key.
     *
     * @param $key
     * @return $this
     */
    protected function key($key)
    {
        $this->key = $key;
        $this->publicKey = $key;

        return $this;
    }

    /**
     * Add the public filter key.
     *
     * @param $key
     * @return $this
     */
    public function as($key)
    {
        $this->publicKey = $key;

        return $this;
    }

    /**
     * Add the public filter key.
     *
     * @param $value
     * @param $callback
     * @return $this
     */
    public function when($value, $callback)
    {
        $this->conditionals[$value] = $callback;

        return $this;
    }

    /**
     * Make a boolean filter type.
     *
     * @return $this
     */
    public function boolean()
    {
        $this->withRules('nullable|in:true,false');

        $this->withQuery(function ($query) {
            $query->where(
                $this->key,
                $this->resolveBoolean($this->value)
            );
        });

        return $this;
    }

    /**
     * Make a toggle filter type.
     *
     * @return $this
     */
    public function toggle()
    {
        $this->withRules('nullable|in:true');

        $this->withQuery(function ($query) {
            $query->where(
                $this->key,
                $this->resolveBoolean($this->value)
            );
        });

        return $this;
    }

    /**
     * Make a option filter type.
     *
     * @return $this
     */
    public function options(array $options)
    {
        $this->withRules('nullable|in:'.implode(',', $options));

        $this->withQuery(function ($query) {
            $query->whereIn($this->key, Arr::wrap($this->value));
        });

        return $this;
    }

    /**
     * Make a scope filter type.
     *
     * @return $this
     */
    public function scope($scope = null)
    {
        $this->withRules('nullable');

        $scope = $scope ?? $this->key;

        $this->withQuery(function ($query) use ($scope) {
            $query->scopes([$scope => $this->value]);
        });

        return $this;
    }

    /**
     * Make a boolean scope filter type.
     *
     * @return $this
     */
    public function scopeBoolean($scope = null)
    {
        $this->withRules('nullable|in:true,false');

        $scope = $scope ?? $this->key;

        $this->withQuery(function ($query) use ($scope) {
            $value = $this->resolveBoolean($this->value);
            if ($value) {
                $query->scopes([$scope => $value]);
            } else {
                $model = $query->getModel();
                $query->whereNotIn($model->getKeyName(), function ($q) use ($model, $scope, $value) {
                    $q->select($model->getKeyName())
                        ->from($model->getTable());

                    $model->callNamedScope($scope, [$q, $value]);
                });
            }
        });

        return $this;
    }

    /**
     * Make a search filter type.
     *
     * @return $this
     */
    public function search($columns)
    {
        $this->withRules('nullable|string|min:1|max:64');

        $this->withQuery(function ($query) use ($columns) {
            (new SearchScope())->apply($query, $columns, $this->value);
        });

        return $this;
    }

    /**
     * Make a range filter type.
     *
     * @return $this
     */
    public function range()
    {
        $this->withNumericMinMax();

        $this->withQuery(function ($query) {
            if (isset($this->value['min'], $this->value['max'])) {
                $query->whereBetween($this->key, [$this->value['min'], $this->value['max']]);
            } elseif (isset($this->value['min'])) {
                $query->where($this->key, '>=', $this->value['min']);
            } elseif (isset($this->value['max'])) {
                $query->where($this->key, '<=', $this->value['max']);
            }
        });

        return $this;
    }

    /**
     * Make a date filter type.
     *
     * @return $this
     */
    public function date()
    {
        $this->withRules('nullable|date');

        $this->withQuery(function (Builder $query) {
            $query->where(function ($query) {
                foreach (Arr::wrap($this->value) as $value) {
                    $query->orWhereDate($this->key, Carbon::parse($value));
                }
            });
        });

        return $this;
    }

    /**
     * Make a date range filter type.
     *
     * @return $this
     */
    public function dateRange()
    {
        $this->modifier('min');
        $this->modifier('max');

        $this->withRules('nullable|date', 'max');
        $this->withRules('nullable|date', 'min');

        $this->withQuery(function ($query) {
            if (isset($this->value['min'], $this->value['max'])) {
                $query->whereBetween($this->key, [
                    Carbon::parse($this->value['min']),
                    Carbon::parse($this->value['max']),
                ]);
            } elseif (isset($this->value['min'])) {
                $query->whereDate($this->key, '>=', Carbon::parse($this->value['min']));
            } elseif (isset($this->value['max'])) {
                $query->whereDate($this->key, '<=', Carbon::parse($this->value['max']));
            }
        });

        return $this;
    }

    /**
     * Make a relationship exists filter type.
     *
     * @param null $relationship
     * @return $this
     */
    public function exists($relationship = null)
    {
        $this->modifier('exists');

        $this->withRules('nullable|in:true,false');

        if (is_null($relationship)) {
            $relationship = $this->key;
        }

        $this->withQuery(function ($query) use ($relationship) {
            $this->value['exists'] === 'true'
                ? $query->has($relationship)
                : $query->doesntHave($relationship);
        });

        return $this;
    }

    /**
     * Make a relationship count filter type.
     *
     * @return $this
     */
    public function count()
    {
        $this->modifier('count');

        $this->withRules('nullable|numeric');

        $this->withQuery(function (Builder $query) {
            $query->has($this->key, '=', $this->value['count']);
        });

        return $this;
    }

    /**
     * Make a relationship count range filter type.
     *
     * @return $this
     */
    public function countRange()
    {
        $this->withNumericMinMax();

        $this->withQuery(function ($query) {
            $modifiers = [
                'min' => '>=', 'max' => '<=',
            ];

            foreach ($this->modifier as $modifier) {
                if ($value = Arr::get($this->value, $modifier)) {
                    $query->has($this->key, $modifiers[$modifier], $value);
                }
            }
        });

        return $this;
    }

    /**
     * Make a related relationship type.
     *
     * @return $this
     */
    public function related()
    {
        $this->withRules('nullable');

        $this->withQuery(function ($query) {
            $this->validateMultiple();

            $key = $query->getModel()->{$this->key}()->getForeignKeyName();

            $query->whereIn($key, Arr::wrap($this->value));
        });

        return $this;
    }

    /**
     * Make a withTrashed filter.
     *
     * @return $this
     */
    public function withTrashed()
    {
        $this->withRules('nullable|in:true');

        $this->withQuery(function ($query) {
            $query->withTrashed();
        });

        return $this;
    }

    /**
     * Make a onlyTrashed filter.
     *
     * @return $this
     */
    public function onlyTrashed()
    {
        $this->withRules('nullable|in:true');

        $this->withQuery(function ($query) {
            $query->onlyTrashed();
        });

        return $this;
    }

    /**
     * Register a modifier.
     *
     * @param $modifier
     * @return $this
     */
    public function modifier($modifier)
    {
        $this->modifiers[] = $modifier;

        return $this;
    }

    /**
     * Set rules for current filter.
     *
     * @param null $rules
     * @param null $modifier
     * @return $this|array
     */
    public function rules($rules, $modifier = null)
    {
        if (! is_array($rules)) {
            $rules = explode('|', $rules);
        }

        $this->rules = function () use ($rules, $modifier) {
            $key = $modifier ? "{$this->publicKey}:${modifier}" : $this->publicKey;

            return [$key => $rules];
        };

        return $this;
    }

    /**
     * Append rules for current filter.
     *
     * @param null $rules
     * @param null $modifier
     * @return $this|array
     */
    public function withRules($rules, $modifier = null)
    {
        if (! is_array($rules)) {
            $rules = explode('|', $rules);
        }

        $this->rules[] = function () use ($rules, $modifier) {
            $key = $modifier ? "{$this->publicKey}:${modifier}" : $this->publicKey;

            return  [$key => $rules];
        };

        return $this;
    }

    /**
     * Get the rules flattened.
     *
     * @return $this|array
     */
    public function getRules()
    {
        $output = [];

        $key = $this->publicKey;

        $isMultiple = ($this->multiple && is_array($this->request->get($key)));

        if (is_callable($this->rules)) {
            $method = $this->rules;

            return $method();
        }

        foreach ($this->rules as $callback) {
            $rules = $callback();
            $key = array_keys($rules)[0];
            $rules = $rules[$key];
            if ($isMultiple) {
                $key = "${key}.*";
            }

            $output[$key] = array_merge(Arr::get($output, $key, []), $rules);
        }

        return $output;
    }

    /**
     * Redirect login if not authenticated user.
     *
     * @return $this
     */
    public function auth()
    {
        $this->authenticated = true;

        return $this;
    }

    /**
     * Allow multiple values for same key.
     *
     * @return $this
     */
    public function multiple()
    {
        $this->multiple = true;

        return $this;
    }

    /**
     * Set the query callback.
     *
     * @param $callback
     * @return $this
     */
    public function query($callback)
    {
        $this->filterMethodCalled = true;

        $this->queries = [$callback];

        return $this;
    }

    /**
     * Add a callback to the query builder.
     *
     * @param $callback
     * @return $this
     */
    public function withQuery($callback)
    {
        $this->filterMethodCalled = true;

        $this->queries[] = $callback;

        return $this;
    }

    /**
     * Apply the filters to the query.
     *
     * @param $query
     */
    public function apply($query)
    {
        $this->prepare();

        if (empty($this->value)) {
            return;
        }

        if ($this->authenticated && ! auth()->check()) {
            throw new UnauthorizedException();
        }

        if (! $this->filterMethodCalled) {
            $this->query = $this->defaultQueryCallback($query);
            $this->applyConditionals($query);

            return;
        }

        foreach ($this->queries as $callback) {
            $callback($query);
        }

        $this->applyConditionals($query);

        $this->query = $query;
    }

    /**
     * Get values from the request.
     */
    protected function prepare()
    {
        $keys = [];

        if (count($this->modifiers) == 0) {
            $keys[] = $this->publicKey;
        }

        foreach ($this->modifiers as $modifier) {
            $keys[] = "{$this->publicKey}:${modifier}";
        }

        $parameters = $this->request->only($keys);

        if (count($parameters) == 0) {
            return; // don't set value & operator
        }

        if (count($this->modifiers) == 0 && count($parameters) == 1) {
            $this->modifier = array_keys($parameters)[0];
            $this->value = array_values($parameters)[0];

            return; // no array values needed because its a single value
        }

        foreach ($parameters as $key => $value) {
            $modifier = str_replace("{$this->publicKey}:", '', $key);
            $this->modifier[] = $modifier;
            $this->value[$modifier] = $value;
        }
    }

    /**
     * Apply default where.
     *
     * @param $query
     * @return Builder;
     */
    protected function defaultQueryCallback($query)
    {
        $this->validateMultiple();

        return $query->whereIn($this->key, Arr::wrap($this->value));
    }

    /**
     * Apply conditionals.
     *
     * @param $query
     * @return Builder;
     */
    protected function applyConditionals($query)
    {
        foreach ($this->conditionals as $value => $callback) {
            if (in_array($value, Arr::wrap($this->value))) {
                $callback($query);
            }
        }

        return $query;
    }

    /**
     * Validate multiple.
     */
    protected function validateMultiple()
    {
        if (! $this->multiple && is_array($this->value)) {
            throw ValidationException::withMessages([
                $this->publicKey => 'Multiple not permitted.',
            ]);
        }
    }

    /**
     * Convert truthy / falsy values.
     *
     * @param $value
     * @return int
     */
    public function resolveBoolean($value)
    {
        return ['false' => 0, 'true' => 1][$value];
    }

    /**
     * Set the request.
     *
     * @param $request
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Adds min max modifier and validation.
     */
    public function withNumericMinMax(): void
    {
        $this->modifier('min');
        $this->modifier('max');

        $minRules = ['nullable', 'numeric', function ($attribute, $value, $fail) {
            if ($this->request->filled("{$this->key}:max") && $value > $this->request->input("{$this->key}:max")) {
                $fail('Must be less than max.');
            }
        }];

        $maxRules = ['nullable', 'numeric', function ($attribute, $value, $fail) {
            if ($this->request->filled("{$this->key}:min") && $value < $this->request->input("{$this->key}:min")) {
                $fail('Must be greater than min.');
            }
        }];

        $this->withRules($minRules, 'min');
        $this->withRules($maxRules, 'max');
    }
}
