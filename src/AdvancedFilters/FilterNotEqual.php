<?php

namespace Spatie\QueryBuilder\AdvancedFilters;

use Illuminate\Database\Eloquent\Builder;

/**
 *  Filter used to compare if column value is not equals to given value
 */
class FilterNotEqual extends AdvancedFilter
{
    public function __invoke(Builder $query, $type): Builder
    {
        $query->{$this->getClausuleType($type)}($this->getColumnName(), '!=', $this->getParsedValue($query));
        return $query;
    }
}