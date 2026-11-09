<?php

namespace Encore\Admin\Grid;

use Closure;
use Encore\Admin\Grid\Filter\AbstractFilter;

/**
 * Class Filter.
 *
 * `$grid->filter(function (Grid\Filter $filter) { $filter->equal(...); ... })`
 */
class Filter
{
    /**
     * @var AbstractFilter[]
     */
    protected array $filters = [];

    /**
     * @var bool
     */
    protected bool $expand = false;

    /**
     * Without a column filter (original `filter_without_column` scope is
     * not applicable to the Filament bridge).
     *
     * @param string      $type
     * @param string      $column
     * @param string|null $label
     *
     * @return AbstractFilter
     */
    protected function addFilter(string $type, string $column, ?string $label = null): AbstractFilter
    {
        return $this->filters[] = new AbstractFilter($type, $column, $label);
    }

    /**
     * @param string     $column
     * @param string|null $label
     *
     * @return AbstractFilter
     */
    public function equal(string $column, ?string $label = null): AbstractFilter
    {
        return $this->addFilter('equal', $column, $label);
    }

    /**
     * @param string     $column
     * @param string|null $label
     *
     * @return AbstractFilter
     */
    public function notEqual(string $column, ?string $label = null): AbstractFilter
    {
        return $this->addFilter('notEqual', $column, $label);
    }

    /**
     * @param string     $column
     * @param string|null $label
     *
     * @return AbstractFilter
     */
    public function like(string $column, ?string $label = null): AbstractFilter
    {
        return $this->addFilter('like', $column, $label);
    }

    /**
     * @param string     $column
     * @param string|null $label
     *
     * @return AbstractFilter
     */
    public function ilike(string $column, ?string $label = null): AbstractFilter
    {
        return $this->addFilter('ilike', $column, $label);
    }

    /**
     * @param string     $column
     * @param string|null $label
     *
     * @return AbstractFilter
     */
    public function gt(string $column, ?string $label = null): AbstractFilter
    {
        return $this->addFilter('gt', $column, $label);
    }

    /**
     * @param string     $column
     * @param string|null $label
     *
     * @return AbstractFilter
     */
    public function lt(string $column, ?string $label = null): AbstractFilter
    {
        return $this->addFilter('lt', $column, $label);
    }

    /**
     * @param string     $column
     * @param string|null $label
     *
     * @return AbstractFilter
     */
    public function startsWith(string $column, ?string $label = null): AbstractFilter
    {
        return $this->addFilter('startsWith', $column, $label);
    }

    /**
     * @param string     $column
     * @param string|null $label
     *
     * @return AbstractFilter
     */
    public function endsWith(string $column, ?string $label = null): AbstractFilter
    {
        return $this->addFilter('endsWith', $column, $label);
    }

    /**
     * @param string     $column
     * @param string|null $label
     *
     * @return AbstractFilter
     */
    public function between(string $column, ?string $label = null): AbstractFilter
    {
        return $this->addFilter('between', $column, $label);
    }

    /**
     * @param string     $column
     * @param string|null $label
     *
     * @return AbstractFilter
     */
    public function date(string $column, ?string $label = null): AbstractFilter
    {
        return $this->addFilter('date', $column, $label);
    }

    /**
     * @param string     $column
     * @param string|null $label
     *
     * @return AbstractFilter
     */
    public function datetime(string $column, ?string $label = null): AbstractFilter
    {
        return $this->addFilter('datetime', $column, $label);
    }

    /**
     * Custom query filter. The closure receives the query builder and the
     * input value.
     *
     * @param Closure    $queryCallback function ($query, $value) {}
     * @param string|null $column
     * @param string|null $label
     *
     * @return AbstractFilter
     */
    public function where(Closure $queryCallback, ?string $column = null, ?string $label = null): AbstractFilter
    {
        $filter = $this->addFilter('where', $column ?? 'where', $label);

        $filter->where($queryCallback);

        return $filter;
    }

    /**
     * @param string     $column
     * @param array|Closure $options
     *
     * @return AbstractFilter
     */
    public function in(string $column, array|Closure $options = []): AbstractFilter
    {
        return $this->addFilter('in', $column)->select($options);
    }

    /**
     * @param string     $column
     * @param array|Closure $options
     *
     * @return AbstractFilter
     */
    public function notIn(string $column, array|Closure $options = []): AbstractFilter
    {
        return $this->addFilter('notIn', $column)->select($options);
    }

    /**
     * Expand the filter panel by default.
     *
     * @return $this
     */
    public function expand(): static
    {
        $this->expand = true;

        return $this;
    }

    /**
     * @return AbstractFilter[]
     */
    public function filters(): array
    {
        return $this->filters;
    }

    /**
     * @return bool
     */
    public function isExpand(): bool
    {
        return $this->expand;
    }
}
