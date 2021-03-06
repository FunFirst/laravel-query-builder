<?php

namespace Spatie\QueryBuilder\AdvancedFilters;

use Illuminate\Database\Eloquent\Builder;

/**
 *  Filter used to compare if column value is equals to given value
 */
class FilterIsUnknown extends AdvancedFilter
{
    public function __invoke(Builder $query, $type): Builder
    {
        $query->{$this->getClausuleType($type)}(function ($q) {
            $q->where($this->getColumnName(), null)
                ->orWhere($this->getColumnName(), '');
        });
        return $query;
    }
}
