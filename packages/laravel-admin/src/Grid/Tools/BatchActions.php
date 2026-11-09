<?php

namespace Encore\Admin\Grid\Tools;

/**
 * Class BatchActions.
 */
class BatchActions
{
    /**
     * @var bool
     */
    protected bool $deleteDisabled = false;

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
    public function isDeleteDisabled(): bool
    {
        return $this->deleteDisabled;
    }
}
