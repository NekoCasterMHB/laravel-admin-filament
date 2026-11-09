<?php

namespace Encore\Admin\Layout;

use Closure;

/**
 * Class Row.
 */
class Row
{
    /**
     * @var Column[]
     */
    protected array $columns = [];

    /**
     * Fixed content spanning the whole row.
     *
     * @var mixed
     */
    protected mixed $contents = null;

    /**
     * Row constructor.
     *
     * @param mixed $content string|view|Closure(Row)
     */
    public function __construct(mixed $content = null)
    {
        if ($content instanceof Closure) {
            $content($this);
        } else {
            $this->column(12, $content);
        }
    }

    /**
     * Add a column with a width of 1..12 (original grid semantics).
     *
     * @param int   $width
     * @param mixed $content string|view|Closure(Column)
     *
     * @return $this
     */
    public function column(int $width, $content): static
    {
        $this->columns[] = new Column($width, $content);

        return $this;
    }

    /**
     * @return Column[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }
}
