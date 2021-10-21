# Laravel Formation

<p>
    <a href="https://github.com/dillingham/formation/actions">
        <img src="https://github.com/dillingham/formation/workflows/tests/badge.svg" alt="Build Status">
    </a>
    <a href="https://packagist.org/packages/dillingham/formation">
        <img src="https://img.shields.io/packagist/v/dillingham/formation" alt="Latest Stable Version">
    </a>
    <a href="https://packagist.org/packages/dillingham/formation">
        <img src="https://img.shields.io/packagist/dt/dillingham/formation" alt="Total Downloads">
    </a>
    <a href="https://twitter.com/im_brian_d">
        <img src="https://img.shields.io/twitter/follow/im_brian_d?color=%231da1f1&label=Twitter&logo=%231da1f1&logoColor=%231da1f1&style=flat-square" alt="twitter">
    </a>
</p>

Add search, sort, filters & more with a typehint in your controller like Laravel's [FormRequest](https://laravel.com/docs/validation#form-request-validation).

Remove all that logic from your models / controllers, and auto-validate query parameters!

This package handles many common scenarios and is setup in a minimal Laravel way.


- ✅ Search columns
- ✅ Search relationships columns 
- ✅ Sort by columns
- ✅ Sort by relationship columns
- ✅ Sort by relationship counts
- ✅ Filter by dates
- ✅ Filter by date ranges
- ✅ Filter by relationships
- ✅ Filter by relationship counts
- ✅ Filter by latitude & longitude
- ✅ Filter by radius
- ✅ Filter by soft deleted
- ✅ Filter by within scope
- ✅ Filter by outside scope
- ✅ Filter by muliple values
- ✅ And so much more!

---

[Install](#install) | [Search](#search) | [Sort](#sort) | [Filters](#filters) | [Helpers](#helpers)

---

# Install
Add to a Laravel project using composer:
```
composer require dillingham/formation
```

Create a formation class using artisan:

```
php artisan make:formation ArticleFormation
```


```php
<?php

namespace App\Formations;

use Dillingham\Formation\Filter;
use Dillingham\Formation\Formation;

class ArticleFormation extends Formation
{
    /**
     * The model class,
     *
     * @var array
     */
    public $model = \App\Models\Article::class;
    
    /**
     * The column to use for select options,
     *
     * @var array
     */
    public $display = 'title';
    
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
     * Define the filters.
     *
     * @return array
     */
    public function filters():array
    {
        return [
            Filter::make('author')->related(),
            Filter::make('published_at')->dateRange(),
            Filter::make('comments')->countRange(),
        ];
    }
}
```

---

#### The results

All results are [paginated](https://laravel.com/docs/eloquent-resources#pagination) and nested within `data`.

Simply typehint in your controller and call `->results()` to execute.

```php
<?php

class ArticleController
{
    public function index(ArticleFormation $request)
    {
        return view('articles.index', [
            'articles' => $request->results()
        ]);
    }
}
```

The URL controls the `results` depending on the settings below.
# Search
Define columns or relationship columns to search:

```php
public $search = ['title', 'comments.body'];
```

```
/articles?search=laravel
```

---

# Sort

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

#### Sorting Relationships
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

## General Filters

Enable query parameters by adding to `filters()`:
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
Results where a column equals one of the optionst:
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

### between
Results by a named range set
```php
Filter::make('length')
    ->between('small', [1, 100])
    ->between('medium', [101, 200])
    ->between('large', [201, 300])
```
```
/articles?length=small
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

## Relationship Filters

Below are a few filters for Laravel's Eloquent relationships.

### related
Results by a relationship primary key:
```php
Filter::make('author')->related(),
```
```
/articles?author=1
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
Results by a relationship count
```php
Filter::make('comments')->count(),
```
```
/articles?comments:count=5
```

### countRange
Results by a relationship count min / max range
```php
Filter::make('tags')->countRange(),
```
```
/articles?tags:min=5&tags:max=10
```

## Location Filters

These filters use geo location to filter Laravel models.

### radius
Results within a latitude, longtitude & distance.
```php
Filter::radius(),
```
```
/users?latitude=40.7517&longitude=-73.9755&distance=10
```

### bounds
Results within a set of map boundaries.
```php
Filter::bounds(),
```
```
/users?ne_lat=40.75555971122113&ne_lng=-73.96922446090224&sw_lat=40.74683062112093&sw_lng=-73.98124075728896
```
`ne_lat`, `ne_lng`, `sw_lat`, `sw_lng`


## Scope Filters

These filters use Laravel's model scope.

### scope
Results where a model scope is applied
```php
Filter::make('status')->scope(),
```
```
/articles?status=active
```

```php
public function scopeStatus($query, $value)
{
    $query->where('status', $value);
}
```


### scopeBoolean
Results in scope = `true`, or not in scope = `false`
```php
Filter::make('published')->scopeBoolean(),
```
```
/articles?published=true
```
```
/articles?published=false
```

>  `false` produces the opposite and returns all results that are not in the scope


## Soft Delete Filters

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

---

# Helpers

Here are some scenarios and recipies:

### Adding authentication restriction
Redirects to route('login') if unauthenticated.
```php
Filter::make('like')->exists()->auth(),
```

### Using a different public key

A url key different from the relationship or column name
```php
Filter::make('status', 'status_id'),
Filter::make('author', 'activeAuthor'),
```
```
/articles?status=1
```

### Adding a multiple values

Allow the filter to accept many values for the same key
```php
Filter::make('status')->multiple(),
```
```
/articles?status[]=active&status[]=draft
```

### Adding filter rules
Appends the rules set by the filter types.
```php
Filter::make('status')->withRules('in:active,inactive'),
```

### Adding value conditions
Add conditions based on the parameter value.
```php
Filter::make('status')
    ->when('active', function($query) {
        return $query->where('status', 'active');
    }),
```

### Appending the query
Add to the query to apply additional conditions.
```php
Filter::make('published')
    ->withQuery(function($query) {
        $query->whereNotNull('published_at');
    }),
```

### Converting to cents
When the public value is in dollars and db is in cents
```php
Filter::make('price')->asCents(),
```
```
/products?price=100 // where('price', 10000)
```

### Adding hardcoded filters

Useful for defining routes with filters: `/active`

```php
public function active(ArticleFormation $request)
{
    $request->merge(['status' => 'active']);

    return $request->results();
}
```

### Adding query conditionals

For hard coding conditions within controllers:

```php
public function index($author_id, ArticleFormation $request)
{
    $request->where('author_id', $author->id);

    return $request->results();
}
```

### Formatting select options

Use `options()` to return results in the following format:
```php
$request->options()->results()
```
```json
{
    "data": [
        {
            "display": 'Article with ID 1',
            "value": 1
        }
    ],
    "links": {...},
    "meta": {...}
}
```

### Automatically route select options

Publish the config automatically with the following command:
```
php artisan vendor:publish --tag=formations
```
Then add a route using this package's `SelectOptionController`
```php
use \Dillingham\Formation\Http\Controllers\SelectOptionController;
```
```php
Route::get('options/{resource}', SelectOptionController::class);
```
The `{resource}` will reference the key in the formations config
```php
'options' => [
    'users' => \App\Formations\UserFormation::class,
]
```

`/options/users` routes to the `UserFormation` + allow search / filters etc

# Author

Hi, [@im_brian_d](https://twitter.com/im_brian_d), software developer and Laravel enthusiast.
