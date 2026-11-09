<?php

namespace Encore\Admin;

use Closure;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Tree.
 *
 * The laravel-admin tree widget. The port renders a nested list via
 * {@see Tree::render()}; drag-and-drop reordering is provided by the
 * tree page (MenuController). saveOrder() keeps the original `_order`
 * payload contract used by ModelTree.
 */
class Tree
{
    /**
     * Tree model.
     *
     * @var Model
     */
    protected Model $model;

    /**
     * @var \Closure|null
     */
    protected ?Closure $queryCallback = null;

    /**
     * @var \Closure|null
     */
    protected ?Closure $branchCallback = null;

    /**
     * @var bool
     */
    protected bool $createDisabled = false;

    /**
     * Tree constructor.
     *
     * @param Model|null    $model
     * @param \Closure|null $callback
     */
    public function __construct(Model $model = null, ?Closure $callback = null)
    {
        $this->model = $model;

        if ($callback instanceof \Closure) {
            call_user_func($callback, $this);
        }
    }

    /**
     * Set query callback to model.
     *
     * @param \Closure|null $query
     *
     * @return $this
     */
    public function query(\Closure $query = null): static
    {
        $this->queryCallback = $query;

        return $this;
    }

    /**
     * Disable the create button.
     *
     * @param bool $disabled
     *
     * @return $this
     */
    public function disableCreate(bool $disabled = true): static
    {
        $this->createDisabled = $disabled;

        return $this;
    }

    /**
     * Set the branch renderer: function (array $branch) { return $payload; }
     *
     * @param \Closure $callback
     *
     * @return $this
     */
    public function branch(\Closure $callback): static
    {
        $this->branchCallback = $callback;

        return $this;
    }

    /**
     * Save tree order from a nested-array payload (the same JSON structure
     * the original Nestable drag-and-drop submitted via `_order`).
     *
     * @param string $serialize
     *
     * @return bool
     */
    public function saveOrder($serialize)
    {
        $tree = json_decode($serialize, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \InvalidArgumentException(json_last_error_msg());
        }

        $this->model->saveOrder($tree);

        return true;
    }

    /**
     * Collect tree data as a nested array.
     *
     * @return array
     */
    public function treeData(): array
    {
        $model = $this->model;

        if ($this->queryCallback instanceof \Closure) {
            $model = call_user_func($this->queryCallback, $model);
        }

        return $model->toTree();
    }

    /**
     * Render the tree payload for a branch.
     *
     * @param array $branch
     *
     * @return string
     */
    public function renderBranch(array $branch): string
    {
        if ($this->branchCallback instanceof \Closure) {
            return (string) ($this->branchCallback)($branch);
        }

        $key = $branch[$this->model->getKeyName()];
        $title = $branch[$this->model->getTitleColumn()];

        return "$key - $title";
    }

    /**
     * Render the nested tree (Tailwind-styled nested list).
     *
     * @return string
     */
    public function render()
    {
        return view('admin.tree.branch-container', ['tree' => $this])->render();
    }
}
