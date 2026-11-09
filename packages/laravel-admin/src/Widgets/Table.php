<?php

namespace Encore\Admin\Widgets;

/**
 * Class Table.
 *
 * A simple data table widget.
 */
class Table extends Widget
{
    /**
     * @var string
     */
    protected string $title = '';

    /**
     * @var array
     */
    protected array $headers = [];

    /**
     * @var array
     */
    protected array $rows = [];

    /**
     * Table constructor.
     *
     * @param array $headers
     * @param array $rows
     * @param string $title
     */
    public function __construct(array $headers = [], array $rows = [], string $title = '')
    {
        $this->headers = $headers;
        $this->rows = $rows;
        $this->title = $title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param array $headers
     *
     * @return $this
     */
    public function setHeaders(array $headers): static
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @param array $rows
     *
     * @return $this
     */
    public function setRows(array $rows): static
    {
        $this->rows = $rows;

        return $this;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        return view('admin.widgets.table', ['table' => $this])->render();
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return array
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }
}
