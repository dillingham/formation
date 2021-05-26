<?php

namespace Dillingham\ListRequest;

class Range
{
    /**
     * The query string id.
     * @var
     */
    public $key;

    /**
     * The column to filter by.
     * @var
     */
    public $column;

    /**
     * The array of values.
     * @var
     */
    public $range;

    /**
     * Make a range.
     * @param $key
     * @return Range
     */
    public static function make($key)
    {
        $class = new self;
        $class->key = $key;

        return $class;
    }

    /**
     * Set the range between.
     * @param $column
     * @param array $range
     * @return $this
     */
    public function between($column, array $range)
    {
        $this->column = $column;
        $this->range = $range;

        return $this;
    }

    /**
     * Apply the range to the query.
     * @param $query
     * @return mixed
     */
    public function apply($query)
    {
        return $query->whereBetween($this->column, $this->range);
    }
}
