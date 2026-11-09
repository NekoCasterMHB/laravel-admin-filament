<?php

namespace Encore\Admin\Widgets;

/**
 * Class Collapse.
 */
class Collapse extends Widget
{
    /**
     * @var array<string, mixed> name => content
     */
    protected array $groups = [];

    /**
     * @var string
     */
    protected string $style = 'default';

    /**
     * Collapse constructor.
     *
     * @param array  $groups
     * @param string $style
     */
    public function __construct(array $groups = [], string $style = 'default')
    {
        $this->groups = $groups;
        $this->style = $style;
    }

    /**
     * Add a collapsible group.
     *
     * @param string $name
     * @param mixed  $content
     *
     * @return $this
     */
    public function add(string $name, mixed $content): static
    {
        $this->groups[$name] = $content;

        return $this;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        return view('admin.widgets.collapse', ['collapse' => $this])->render();
    }

    /**
     * @return array<string, mixed>
     */
    public function getGroups(): array
    {
        return $this->groups;
    }
}
