<?php

namespace Encore\Admin\Layout;

use Closure;

/**
 * Class Column.
 */
class Column
{
    /**
     * Column width in the original 12-column grid.
     *
     * @var int
     */
    protected int $width;

    /**
     * @var array
     */
    protected array $contents = [];

    /**
     * Column constructor.
     *
     * @param int   $width
     * @param mixed $content string|view|Closure(Column)
     */
    public function __construct(int $width, $content = null)
    {
        $this->width = $width;

        if ($content instanceof Closure) {
            $content($this);
        } elseif ($content !== null) {
            $this->append($content);
        }
    }

    /**
     * Append content to the column.
     *
     * @param mixed $content string|view
     *
     * @return $this
     */
    public function append($content): static
    {
        $this->contents[] = $content;

        return $this;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @return array
     */
    public function getContents(): array
    {
        return $this->contents;
    }
}
