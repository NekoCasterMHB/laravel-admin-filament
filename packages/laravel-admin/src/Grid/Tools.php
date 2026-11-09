<?php

namespace Encore\Admin\Grid;

use Closure;
use Encore\Admin\Grid\Tools\BatchActions;

/**
 * Class Tools.
 *
 * `$grid->tools(function (Tools $tools) { $tools->batch(function (BatchActions $actions) {...}); })`
 */
class Tools
{
    /**
     * @var Closure|null
     */
    protected ?Closure $batchCallback = null;

    /**
     * @var bool
     */
    protected bool $batchDeleteDisabled = false;

    /**
     * Configure the batch (bulk) actions.
     *
     * @param Closure $callback function (BatchActions $actions) {}
     *
     * @return $this
     */
    public function batch(Closure $callback): static
    {
        $actions = new BatchActions();

        $callback($actions);

        $this->batchDeleteDisabled = $actions->isDeleteDisabled();

        return $this;
    }

    /**
     * Whether batch delete is disabled.
     *
     * @return bool
     */
    public function isBatchDeleteDisabled(): bool
    {
        return $this->batchDeleteDisabled;
    }
}
