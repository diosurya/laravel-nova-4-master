<?php

namespace App\Nova\Filters;

use Carbon\Carbon;
use Laravel\Nova\Filters\DateFilter;
use Laravel\Nova\Http\Requests\NovaRequest;

class FilterDateArticle extends DateFilter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $name = 'Sort Entered Date';

    /**
     * Apply the filter to the given query.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(NovaRequest $request, $query, $value)
    {
        return $query->where('entered', '<=', Carbon::parse($value));
    }
}
