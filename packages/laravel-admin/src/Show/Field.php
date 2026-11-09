<?php

namespace Encore\Admin\Show;

use Closure;

/**
 * Class Field.
 *
 * A show (detail) field definition.
 */
class Field
{
    /**
     * @var string
     */
    protected string $name;

    /**
     * @var string|null
     */
    protected ?string $label = null;

    /**
     * Display callback (bound to the record, original contract).
     *
     * @var Closure|null
     */
    protected ?Closure $callback = null;

    /**
     * @var bool
     */
    protected bool $isLabel = false;

    /**
     * @var bool
     */
    protected bool $unescape = false;

    /**
     * Field constructor.
     *
     * @param string      $name
     * @param string|null $label
     */
    public function __construct(string $name, ?string $label = null)
    {
        $this->name = $name;
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * Custom display callback.
     *
     * @param Closure $callback
     *
     * @return $this
     */
    public function as(Closure $callback): static
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Display as label/badge.
     *
     * @return $this
     */
    public function label(): static
    {
        $this->isLabel = true;

        return $this;
    }

    /**
     * Render value as unescaped HTML.
     *
     * @return $this
     */
    public function unescape(): static
    {
        $this->unescape = true;

        return $this;
    }

    /**
     * @return Closure|null
     */
    public function getCallback(): ?Closure
    {
        return $this->callback;
    }

    /**
     * @return bool
     */
    public function isLabel(): bool
    {
        return $this->isLabel;
    }

    /**
     * @return bool
     */
    public function isUnescape(): bool
    {
        return $this->unescape;
    }
}
