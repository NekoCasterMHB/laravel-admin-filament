<?php

namespace Encore\Admin\Grid;

use Closure;
use Encore\Admin\Grid;

/**
 * Class Column.
 *
 * The ported equivalent of the original Grid column DSL. Displayers map to
 * Filament table columns in Grid::applyToTable().
 */
class Column
{
    /**
     * Column name (may be a relation like "user.name" or "roles").
     *
     * @var string
     */
    protected string $name;

    /**
     * Column label.
     *
     * @var string|null
     */
    protected ?string $label = null;

    /**
     * @var bool
     */
    protected bool $sortable = false;

    /**
     * Custom display callback (may return HTML).
     *
     * @var Closure|null
     */
    protected ?Closure $displayCallback = null;

    /**
     * Display as label/badge with a color style.
     *
     * @var string|null
     */
    protected ?string $labelStyle = null;

    /**
     * Pluck a column of the related models (relation columns).
     *
     * @var string|null
     */
    protected ?string $pluckColumn = null;

    /**
     * Column width hint (original `width()`), informational for Filament.
     *
     * @var string|null
     */
    protected ?string $width = null;

    /**
     * Displayer flags/config collected by the helpers below.
     *
     * @var array
     */
    protected array $displayers = [];

    /**
     * Column constructor.
     *
     * @param string     $name
     * @param string|null $label
     */
    public function __construct(string $name, ?string $label = null)
    {
        $this->name = $name;
        $this->label = $label;
    }

    /**
     * Get column name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Generic displayer registration.
     *
     * @param string $name
     * @param mixed  $config
     *
     * @return $this
     */
    public function setDisplayer(string $name, mixed $config = true): static
    {
        $this->displayers[$name] = $config;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function getDisplayer(string $name): mixed
    {
        return $this->displayers[$name] ?? null;
    }

    /**
     * @return array
     */
    public function getDisplayers(): array
    {
        return $this->displayers;
    }

    /**
     * Render as an image.
     *
     * @param string|int|null $width
     * @param string|int|null $height
     *
     * @return $this
     */
    public function image(string|int|null $width = null, string|int|null $height = null): static
    {
        return $this->setDisplayer('image', ['width' => $width, 'height' => $height]);
    }

    /**
     * Render the value as a link (the value is the URL, or use the
     * `link()` config of [href, text]).
     *
     * @param string|Closure|null $href
     * @param string|Closure|null $text
     *
     * @return $this
     */
    public function link(string|Closure|null $href = null, string|Closure|null $text = null): static
    {
        return $this->setDisplayer('link', ['href' => $href, 'text' => $text]);
    }

    /**
     * Render as a boolean checkbox icon.
     *
     * @param string $style
     *
     * @return $this
     */
    public function checkbox(string $style = 'primary'): static
    {
        return $this->setDisplayer('checkbox', $style);
    }

    /**
     * Inline toggle writing to the model (original switch displayer).
     *
     * @param mixed $states [on-value, off-value] — defaults to true/false
     *
     * @return $this
     */
    public function switch(mixed $states = [true, false]): static
    {
        return $this->setDisplayer('switch', $states);
    }

    /**
     * Inline editable text (original editable displayer).
     *
     * @return $this
     */
    public function editable(): static
    {
        return $this->setDisplayer('editable', true);
    }

    /**
     * Inline editable select (original select displayer).
     *
     * @param array $options
     *
     * @return $this
     */
    public function selectEditable(array $options): static
    {
        return $this->setDisplayer('selectEditable', $options);
    }

    /**
     * Copy-to-clipboard on click.
     *
     * @return $this
     */
    public function copyable(): static
    {
        return $this->setDisplayer('copyable', true);
    }

    /**
     * Download link (the value is the file path on the admin disk).
     *
     * @return $this
     */
    public function downloadable(): static
    {
        return $this->setDisplayer('downloadable', true);
    }

    /**
     * Truncate to N characters.
     *
     * @param int    $limit
     * @param string $end
     *
     * @return $this
     */
    public function limit(int $limit, string $end = '...'): static
    {
        return $this->setDisplayer('limit', ['limit' => $limit, 'end' => $end]);
    }

    /**
     * Mask the value (original secret displayer).
     *
     * @param string $mask
     *
     * @return $this
     */
    public function secret(string $mask = '******'): static
    {
        return $this->setDisplayer('secret', $mask);
    }

    /**
     * Prepend a prefix to the value.
     *
     * @param string|Closure $prefix
     *
     * @return $this
     */
    public function prefix(string|Closure $prefix): static
    {
        return $this->setDisplayer('prefix', $prefix);
    }

    /**
     * Append a suffix to the value.
     *
     * @param string|Closure $suffix
     *
     * @return $this
     */
    public function suffix(string|Closure $suffix): static
    {
        return $this->setDisplayer('suffix', $suffix);
    }

    /**
     * Badge with an explicit color (original badge displayer).
     *
     * @param string|null $style
     *
     * @return $this
     */
    public function badge(?string $style = null): static
    {
        return $this->setDisplayer('badge', $style);
    }

    /**
     * Drag-order handle (original orderable displayer; requires an order
     * column on the model).
     *
     * @param string $column
     *
     * @return $this
     */
    public function orderable(string $column = 'order'): static
    {
        return $this->setDisplayer('orderable', $column);
    }

    /**
     * Progress bar (original progressbar displayer; value 0-100).
     *
     * @return $this
     */
    public function progressbar(): static
    {
        return $this->setDisplayer('progressbar', true);
    }

    /**
     * Get column label.
     *
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * Mark column sortable.
     *
     * @param bool $sortable
     *
     * @return $this
     */
    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;

        return $this;
    }

    /**
     * Custom display callback. The closure receives the value and is bound
     * to the row model (original behavior), so `$this->column` works inside.
     *
     * @param Closure $callback
     *
     * @return $this
     */
    public function display(Closure $callback): static
    {
        $this->displayCallback = $callback;

        return $this;
    }

    /**
     * Display the value as a colored label (badge).
     *
     * @param string $style success|info|primary|warning|danger|...
     *
     * @return $this
     */
    public function label(string $style = 'success'): static
    {
        $this->labelStyle = $style;

        return $this;
    }

    /**
     * Display a plucked column of the related models as labels.
     *
     * @param string $column
     *
     * @return $this
     */
    public function pluck(string $column): static
    {
        $this->pluckColumn = $column;

        return $this;
    }

    /**
     * Set column width (kept for API compatibility; informational).
     *
     * @param string $width
     *
     * @return $this
     */
    public function width(string $width): static
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSortable(): bool
    {
        return $this->sortable;
    }

    /**
     * @return Closure|null
     */
    public function getDisplayCallback(): ?Closure
    {
        return $this->displayCallback;
    }

    /**
     * @return string|null
     */
    public function getLabelStyle(): ?string
    {
        return $this->labelStyle;
    }

    /**
     * @return string|null
     */
    public function getPluckColumn(): ?string
    {
        return $this->pluckColumn;
    }
}
