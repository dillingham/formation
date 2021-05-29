# ListRequest

<p>
    <a href="https://github.com/dillingham/list-request/actions">
        <img src="https://github.com/dillingham/list-request/workflows/tests/badge.svg" alt="Build Status">
    </a>
    <a href="https://packagist.org/packages/dillingham/list-request">
        <img src="https://img.shields.io/packagist/v/dillingham/list-request" alt="Latest Stable Version">
    </a>
    <a href="https://packagist.org/packages/dillingham/list-request">
        <img src="https://img.shields.io/packagist/dt/dillingham/list-request" alt="Total Downloads">
    </a>
    <a href="https://twitter.com/im_brian_d">
        <img src="https://img.shields.io/twitter/follow/im_brian_d?color=%231da1f1&label=Twitter&logo=%231da1f1&logoColor=%231da1f1&style=flat-square" alt="twitter">
    </a>
</p>

Add search, sort, filters & more with a typehint in your controller like a [FormRequest](https://laravel.com/docs/validation#form-request-validation).

Remove all that logic from your models / controllers, and validate your query parameters!

✅ Search relationships ✅ Filter date ranges ✅ Sort by relationship counts ✅ Filter trashed 

And so much more! This package solves many common scenarios with a minimal and simple setup.

---

[Install](#install) | [Configuration](#configuration) | [Cookbook](#cookbook)

---

# Install
Add to a Laravel project using composer:
```
composer require dillingham/list-request
```

Create a request class using artisan `--list`:

```
php artisan make:request ListArticleRequest --list
``` 

Then typehint in your controller and call `->results()` to execute.

```php
<?php

class ArticleController
{
    public function index(ListArticleRequest $request) 
    {
        return view('articles.index', [
            'articles' => $request->results() 
        ]);
    }
}
```
And here is an example of a configured ListRequest:

```php
<?php

namespace App\Http\Requests;

use Dillingham\ListRequest\Filter;
use Dillingham\ListRequest\ListRequest;
use Dillingham\ListRequest\Range;

class ListArticleRequest extends ListRequest
{
    /**
     * The searchable columns,
     *
     * @var array
     */
    public $search = ['title', 'comments.body'];

    /**
     * The sortable columns.
     *
     * @var array
     */
    public $sort = ['created_at', 'comments'];

    /**
     * Define the query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function builder()
    {
        return Article::query();
    }

    /**
     * Define the filters.
     *
     * @return array
     */
    public function filters()
    {
        return [
            Filter::make('author')->related(),
            Filter::make('published_at')->dateRange(),
            Filter::make('comments')->countRange(),
        ];
    }

    /**
     * Define the ranges
     *
     * @return array
     */
    public function ranges()
    {
        return [
            Range::make('this-week')
                ->between('published_at', [
                    today()->subWeek(), 
                    today()
                ])
        ];
    }
}
```

---


# Configuration

Below are the many options available for configuring ListRequests.

#### The results

All results are [paginated](https://laravel.com/docs/eloquent-resources#pagination) and nested within `data`.

## Search
Define columns or relationship columns to search:

```php
public $search = ['title', 'comments.body'];
```

```
/articles?search=laravel
```

---

## Sort

Define which columns are sortable:
```php
public $sort = ['name', 'created_at'];
```

```
/articles?sort=created_at
```

#### Descending Order
Change `sort` to `sort-desc` changes it's direction.
```
/articles?sort-desc=created_at
```

#### Relationships
Sort relationship counts, relationship columns and or alias them:

```php
public $sort = [
    'comments', 
    'comments.upvotes', 
    'comments.downvotes as disliked',
];
```
`sort=comments` `sort=upvotes` `sort=disliked`

---

## Filters

Enable parameters for a list by adding to it's `filters()`:
```php
public function filters()
{
    return [
        Filter::make('active')->boolean(),        
    ];
}
```
### boolean
Results where a boolean value is true or false:
```php
Filter::make('active')->boolean(),
```
```
/articles?active=true
```

### toggle
Same a boolean() but only allows `true`:
```php
Filter::make('active')->toggle(),
```
```
/articles?active=true
```

### options
Results where a column equals a value from a list:
```php
Filter::make('status')->options(['published', 'draft']),
```
```
/articles?status=draft
```

### range
Results where a number has a min and max value:
```php
Filter::make('length')->range(),
```
```
/articles?length:min=5&length:max=100
```

### date
Results where a specific date is filtered by:
```php
Filter::make('published_at')->date(),
```
```
/articles?published_at=01/01/2021
```

### dateRange
Results where date range is filtered by:
```php
Filter::make('published_at')->dateRange(),
```
```
/articles?published_at:min=01/01/2021&published_at:max=02/01/2021
```

### search
Results where a group of columns are searched
```php
Filter::make('written-by')->search([
    'author_id',
    'author.name', 
    'author.username'
]),
```
```
/articles?written-by=Brian
```

### withTrashed
Results include soft deleted and not deleted
```php
Filter::make('with-deleted')->withTrashed(),
```
```
/articles?with-deleted=true
```

### onlyTrashed
Results only include soft deleted
```php
Filter::make('deleted')->onlyTrashed(),
```
```
/articles?deleted=true
```

### exists
Results where a relationship exists
```php
Filter::make('like')->exists(),
```
```
/articles?like:exists=true
```

### count
Results by a has many relationship count
```php
Filter::make('comments')->count(),
```
```
/articles?comments:count=5
```

### countRange
Results by a has many relationship range
```php
Filter::make('comments')->countRange(),
```
```
/articles?comments:min=5&comments:max=10
```

### related
Results where a relationship is used:
```php
Filter::make('author')->related(),
```
```
/articles?author=1
```

### scope
Results where a model scope is applied
```php
Filter::make('published')->scope(),
```
```
/articles?published=true
```

```php
public function scopePublished($query) 
{
    $query->whereNotNull('published_at');
}
```
> The value gets passed as the scope's 2nd parameter, so `false` will apply a scope by default.

### scopeBoolean
Results in scope = `true`, or not in scope = `false`
```php
Filter::make('published')->scopeBoolean(),
```
```
/articles?published=true
```

>  `false` produces the opposite and returns all results that are not in the scope

---

## Ranges

Ranges are pre-defined start and finish values to filter between. 

Here is how to define: `Today`, `7 Days`, `This Month`:

```php
public function ranges()
{
    return [
        Range::make('today')
            ->between('created_at', [
                now()->startOfDay(),
                now()->endOfDay(),
            ]),

        Range::make('7-days')
            ->between('created_at', [
                now()->subDays(7),
                now(),
            ]),

        Range::make('month')
            ->between('created_at', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ]),
    ];
}
```
Use ranges by referencing it's key:
```
/articles?range=today
```
Multiple ranges are supported; same columns are invalid.
```
/articles?range[]=today&range[]=lengthy
```

---

# Cookbook
Here are some scenarios and recipies:

## Adding authentication restriction
Redirects to route('login') if unauthenticated.
```php
Filter::make('like')->exists()->auth(),
```

## Adding a query parameter name

When the filter key is different than the query parameter
```php
Filter::make('status_column')->as('status'),
```
```
/articles?status=active
```

## Adding a multiple values 

Allow the filter to accept many values for the same key
```php
Filter::make('status')->multiple(),
```
```
/articles?status[]=active&status[]=draft
```

## Adding filter rules
Appends the rules set by the filter types.
```php
Filter::make('status')->withRules('in:active,inactive'),
```

## Adding value conditions
Add conditions based on the parameter value.
```php
Filter::make('status')
    ->when('active', function($query) {
        return $query->where('status', 'active');
    }),
```

## Appending the query
Add to the query to apply additional conditions.
```php
Filter::make('published')
    ->withQuery(function($query) {
        $query->whereNotNull('published_at');
    }),
```

## Adding hardcoded filters

Useful for defining routes with filters: `/active`

```php
public function active(ListArticleRequest $request)
{
    $request->merge(['status' => 'active']);
    
    return $request->results();
}
```

# Author


Hi, [@im_brian_d](https://twitter.com/im_brian_d), software developer and Laravel enthusiast. 
