<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class Filters
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Builder
     */
    protected $query;

    /**
     * Filters constructor.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Applies filters to the Query Builder.
     *
     * @return Builder
     */
    public function apply(Builder $query)
    {
        $this->query = $query;

        foreach ($this->filters() as $filter => $value) {
            if (method_exists($this, $filter)) {
                call_user_func([$this, $filter], $value);
            }
        }

        return $this->query;
    }

    /**
     * Gets the filters from the request inputs.
     *
     * @return array
     */
    public function filters()
    {
        return $this->request->except(get_class_methods(self::class));
    }
}
