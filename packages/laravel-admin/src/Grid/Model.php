<?php

namespace Encore\Admin\Grid;

// Port note: aliased — a bare `use ...Eloquent\Model` would collide with
// this file's own `class Model` declaration.
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class Model.
 *
 * Query customization wrapper — `$grid->model()->orderBy(...)` in the
 * original returned a query wrapper; the port captures the calls and
 * replays them onto the Filament table query.
 */
class Model
{
    /**
     * @var EloquentModel
     */
    protected EloquentModel $model;

    /**
     * Captured orderings.
     *
     * @var array
     */
    protected array $orders = [];

    /**
     * Captured where conditions: [column, operator, value].
     *
     * @var array
     */
    protected array $wheres = [];

    /**
     * Model constructor.
     *
     * @param EloquentModel $model
     */
    public function __construct(EloquentModel $model)
    {
        $this->model = $model;
    }

    /**
     * Underlying model instance.
     *
     * @return EloquentModel
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @param string $column
     * @param string $direction
     *
     * @return $this
     */
    public function orderBy($column, $direction = 'asc'): static
    {
        $this->orders[] = [$column, $direction];

        return $this;
    }

    /**
     * @param mixed  $column
     * @param mixed  $operator
     * @param mixed  $value
     * @param string $boolean
     *
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and'): static
    {
        $this->wheres[] = [$column, $operator, $value, $boolean];

        return $this;
    }

    /**
     * Replay captured modifications onto a query.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function apply(Builder $query): Builder
    {
        foreach ($this->wheres as [$column, $operator, $value, $boolean]) {
            $boolean === 'or'
                ? $query->orWhere($column, $operator, $value)
                : $query->where($column, $operator, $value);
        }

        foreach ($this->orders as [$column, $direction]) {
            $query->orderBy($column, $direction);
        }

        return $query;
    }
}
