<?php

namespace App\Nova\Filters;

use App\Models\ArticleCategory;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class CategoryArticle extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    // public $component = 'select-filter';

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
        // return $query->whereHas('ArticleCategory', function($query) use ($value){
        //     $query->where('cat_1_id', $value);
        // });
        return $query->where('cat_1_id', $value);
        // return $query
        //     ->join('ArticleCategory as ac', 'Article.cat_1_id', 'ac.id')
        //     ->where('ac.id', $value)
        //     ->get();
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function options(NovaRequest $request)
    {
        $newOptions = [];
        foreach(ArticleCategory::all() as $categoryArticle){
            $newOptions[$categoryArticle->title] = $categoryArticle->id;
        }
        return  $newOptions;
    }
}
