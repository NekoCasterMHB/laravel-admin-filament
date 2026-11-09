<?php

namespace Encore\Admin\Grid\Displayers;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Actions.
 *
 * Row actions displayer passed to the `$grid->actions(function (Actions $actions) {...})`
 * callback per record. Flags are honored when the Filament row actions are
 * built (Grid::applyToTable).
 */
class Actions
{
    /**
     * Current row model.
     *
     * @var Model
     */
    public Model $row;

    /**
     * @var Grid|null
     */
    protected $grid;

    /**
     * @var bool
     */
    protected bool $editDisabled = false;

    /**
     * @var bool
     */
    protected bool $viewDisabled = false;

    /**
     * @var bool
     */
    protected bool $deleteDisabled = false;

    /**
     * Actions constructor.
     *
     * @param Model     $row
     * @param mixed     $grid
     */
    public function __construct(Model $row, $grid = null)
    {
        $this->row = $row;
        $this->grid = $grid;
    }

    /**
     * Current row primary key.
     *
     * @return mixed
     */
    public function getKey()
    {
        return $this->row->getKey();
    }

    /**
     * Alias of getKey().
     *
     * @return mixed
     */
    public function getRowKey()
    {
        return $this->getKey();
    }

    /**
     * @param bool $disabled
     *
     * @return $this
     */
    public function disableEdit(bool $disabled = true): static
    {
        $this->editDisabled = $disabled;

        return $this;
    }

    /**
     * @param bool $disabled
     *
     * @return $this
     */
    public function disableView(bool $disabled = true): static
    {
        $this->viewDisabled = $disabled;

        return $this;
    }

    /**
     * @param bool $disabled
     *
     * @return $this
     */
    public function disableDelete(bool $disabled = true): static
    {
        $this->deleteDisabled = $disabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEditDisabled(): bool
    {
        return $this->editDisabled;
    }

    /**
     * @return bool
     */
    public function isViewDisabled(): bool
    {
        return $this->viewDisabled;
    }

    /**
     * @return bool
     */
    public function isDeleteDisabled(): bool
    {
        return $this->deleteDisabled;
    }

    /**
     * Whether this row shows no actions at all after the callback ran.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->editDisabled && $this->viewDisabled && $this->deleteDisabled;
    }
}
