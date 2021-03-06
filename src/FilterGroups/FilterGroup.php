<?php

namespace Spatie\QueryBuilder\FilterGroups;

use Spatie\QueryBuilder\AdvancedFilters\AdvancedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class FilterGroup
{
    const TYPE_AND = 'AND';
    const TYPE_OR = 'OR';

    protected $type;
    protected $filters = [];
    protected $filterGroups = [];

    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     *  Add FilterGroup to filterGroups property
     *
     *  @param FilterGroup $filterGroup
     *  @retrun void
     */
    public function addGroup(FilterGroup $filterGroup)
    {
        $this->filterGroups[] = $filterGroup;
    }

    /**
     *  Add AdvancedFilter to filters property
     *
     *  @param AdvancedFilter $filter
     *  @retrun void
     */
    public function addFilter(AdvancedFilter $filter)
    {
        $this->filters[] = $filter;
    }

    public function filter($query)
    {
        $clausuleType = $this->type === self::TYPE_AND ? 'where' : 'orWhere';
        if (!empty($this->filterGroups)) { // Go deeper into child groups
            foreach ($this->filterGroups as $filterGroup) {
                $query->{$clausuleType}(function ($q) use ($filterGroup) {
                    $filterGroup->filter($q);
                });
            }
        } elseif (!empty($this->filters)) { // Use filters inside group on query
            $query->where(function ($q) use ($clausuleType) {
                foreach ($this->getGroupedFiltersByRelationship($q) as $filterGroupName => $filterGroupFilters) {  // Get Filters Grouped based on relationship
                    if ($filterGroupName === '-') {
                        foreach ($filterGroupFilters as $filter) {  // Use filters that are not relationship filters or are custom filters
                            if (!$q->getModel()->applyCustomFilter($q, $filter, $this->type)) { // Apply Custom filter or fallback to Predefined filter
                                $filter($q, $this->type);
                            }
                        }
                    } else {
                        $q->{$clausuleType . 'Has'}($filterGroupName, function ($relationshipQuery) use ($filterGroupFilters) { // Use filters for filters grouped by relationship
                            foreach ($filterGroupFilters as $filter) {
                                $filter($relationshipQuery, $this->type);
                            }
                        });
                    }
                }
            });
        }
    }

    protected function isRelationProperty(Builder $query, string $property) : bool
    {
        if (!Str::contains($property, '.')) {
            return false;
        }

        if (in_array($property, $this->relationConstraints)) {
            return false;
        }

        if (Str::startsWith($property, $query->getModel()->getTable() . '.')) {
            return false;
        }

        return true;
    }

    public function getGroupedFiltersByRelationship($query)
    {
        $groupedFilters = [];
        foreach ($this->filters as $filter) {
            if (str_contains($filter->getProperty(), '.')) {
                $relationship = substr($filter->getProperty(), 0, strrpos($filter->getProperty(), '.'));
                if (method_exists($query->getModel(), $relationship)) {
                    $groupedFilters[$relationship][] = $filter;
                } else {
                    $groupedFilters['-'][] = $filter;    
                }
            } else {
                $groupedFilters['-'][] = $filter;
            }
        }
        return $groupedFilters;
    }
}
