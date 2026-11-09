<?php

namespace Encore\Admin\Grid\Filter;

use Closure;
use Illuminate\Support\Collection;

/**
 * A single grid filter definition.
 *
 * The ported subset of the original Grid\Filter\AbstractFilter: type,
 * column, label, presenter (select/radio/checkbox/datetime) and the custom
 * `where()` query callback.
 */
class AbstractFilter
{
    /**
     * Filter type: equal|notEqual|like|ilike|gt|lt|startsWith|endsWith|
     * between|date|datetime|where|in|notIn.
     *
     * @var string
     */
    protected string $type;

    /**
     * @var string
     */
    protected string $column;

    /**
     * @var string|null
     */
    protected ?string $label = null;

    /**
     * Presenter: select options (or null for a plain text input).
     *
     * @var array|null
     */
    protected ?array $options = null;

    /**
     * Presenter type.
     *
     * @var string
     */
    protected string $presenter = 'text';

    /**
     * Custom query callback for `where()`.
     *
     * @var Closure|null
     */
    protected ?Closure $queryCallback = null;

    /**
     * Default value.
     *
     * @var mixed
     */
    protected mixed $default = null;

    /**
     * Help text.
     *
     * @var string|null
     */
    protected ?string $help = null;

    /**
     * AbstractFilter constructor.
     *
     * @param string      $type
     * @param string      $column
     * @param string|null $label
     */
    public function __construct(string $type, string $column, ?string $label = null)
    {
        $this->type = $type;
        $this->column = $column;
        $this->label = $label ?? $column;
    }

    /**
     * Select presenter.
     *
     * @param array|Collection|Closure $options
     *
     * @return $this
     */
    public function select(array|Collection|Closure $options): static
    {
        $this->presenter = 'select';
        $this->options = $this->normalizeOptions($options);

        return $this;
    }

    /**
     * Multiple select presenter.
     *
     * @param array|Collection|Closure $options
     *
     * @return $this
     */
    public function multipleSelect(array|Collection|Closure $options): static
    {
        $this->presenter = 'multipleSelect';
        $this->options = $this->normalizeOptions($options);

        return $this;
    }

    /**
     * @param mixed $options
     *
     * @return array|null
     */
    protected function normalizeOptions(mixed $options): ?array
    {
        if ($options instanceof Closure) {
            $options = $options();
        }

        if ($options instanceof Collection) {
            $options = $options->all();
        }

        return $options;
    }

    /**
     * Radio presenter.
     *
     * @param array $options
     *
     * @return $this
     */
    public function radio(array $options): static
    {
        $this->presenter = 'radio';
        $this->options = $options;

        return $this;
    }

    /**
     * Checkbox presenter.
     *
     * @param array $options
     *
     * @return $this
     */
    public function checkbox(array $options): static
    {
        $this->presenter = 'checkbox';
        $this->options = $options;

        return $this;
    }

    /**
     * Datetime presenter.
     *
     * @return $this
     */
    public function datetime(): static
    {
        $this->presenter = 'datetime';

        return $this;
    }

    /**
     * Custom query callback (for `where()` filters).
     *
     * @param Closure $callback function ($query, $value) {}
     *
     * @return $this
     */
    public function where(Closure $callback): static
    {
        $this->queryCallback = $callback;

        return $this;
    }

    /**
     * Default value.
     *
     * @param mixed $default
     *
     * @return $this
     */
    public function default(mixed $default): static
    {
        $this->default = $default;

        return $this;
    }

    /**
     * Help text.
     *
     * @param string $help
     *
     * @return $this
     */
    public function help(string $help): static
    {
        $this->help = $help;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @return array|null
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    /**
     * @return string
     */
    public function getPresenter(): string
    {
        return $this->presenter;
    }

    /**
     * @return Closure|null
     */
    public function getQueryCallback(): ?Closure
    {
        return $this->queryCallback;
    }

    /**
     * @return mixed
     */
    public function getDefault(): mixed
    {
        return $this->default;
    }

    /**
     * @return string|null
     */
    public function getHelp(): ?string
    {
        return $this->help;
    }
}
