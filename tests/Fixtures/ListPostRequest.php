<?php

namespace Dillingham\ListRequest\Tests\Fixtures;

use Dillingham\ListRequest\Filter;
use Dillingham\ListRequest\ListRequest;

class ListPostRequest extends ListRequest
{
    public $search = [
        'title',
        'comments.body',
        'tags.title',
    ];

    public $sort = [
        'title',
        'comments',
        'comments.upvotes',
        'comments.downvotes as disliked',
    ];

    public $defaults = [
        'sort-desc' => 'body',
    ];

    public function rules()
    {
        return [
            'rule_test' => 'nullable|in:allowed-value',
        ];
    }

    public function builder()
    {
        return Post::query();
    }

    public function filters()
    {
        return [
            Filter::make('id'),
            Filter::make('author_id')->multiple(),
            Filter::make('like')->exists()->auth(),
            Filter::make('length')->range(),
            Filter::make('author')->related(),
            Filter::make('writer', 'author')->related()->multiple(),
            Filter::make('active')->boolean(),
            Filter::make('toggle', 'active')->toggle(),
            Filter::make('comments')->exists(),
            Filter::make('comments')->count(),
            Filter::make('comments')->countRange(),
            Filter::make('tagged', 'tags')->related()->multiple(),
            Filter::make('tags')->exists(),
            Filter::make('tags')->count(),
            Filter::make('tags')->countRange(),
            Filter::make('published_at')->date(),
            Filter::make('multiple_dates', 'published_at')->date()->multiple(),
            Filter::make('created_at')->dateRange(),
            Filter::make('status')->options(['active', 'inactive']),
            Filter::make('multiple', 'status')->options(['active', 'inactive'])->multiple(),
            Filter::make('value-scope')->scope('status'),
            Filter::make('active-scope')->scope('active'),
            Filter::make('boolean-scope')->scopeBoolean('activeBoolean'),
            Filter::make('trashed')->onlyTrashed(),
            Filter::make('with-trashed')->withTrashed(),
            Filter::make('written-by')->search(['author.name']),
            Filter::make('article-size', 'length')
                ->when('50', function ($query) {
                    $query->where('length', '50');
                })->when('100', function ($query) {
                    $query->where('length', '100');
                }),

            Filter::make('length-range', 'length')
                ->between('small', [1,10])
                ->between('medium', [11,20])
                ->between('large', [21,30]),

            Filter::make('length-range', 'length')
                ->between('small', [1,10])
                ->between('medium', [11,20])
                ->between('large', [21,30]),

            Filter::make('money', 'length')->asCents(),

            Filter::radius(),

            Filter::bounds(),
        ];
    }
}
