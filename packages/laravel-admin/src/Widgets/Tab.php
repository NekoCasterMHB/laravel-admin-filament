<?php

namespace Encore\Admin\Widgets;

/**
 * Class Tab.
 */
class Tab extends Widget
{
    /**
     * @var array<string, mixed> name => content
     */
    protected array $tabs = [];

    /**
     * @var string|null
     */
    protected ?string $active = null;

    /**
     * Add a tab.
     *
     * @param string $name
     * @param mixed  $content
     *
     * @return $this
     */
    public function add(string $name, mixed $content): static
    {
        $this->tabs[$name] = $content;

        if ($this->active === null) {
            $this->active = $name;
        }

        return $this;
    }

    /**
     * Alias of add().
     *
     * @param string $name
     * @param mixed  $content
     *
     * @return $this
     */
    public function tab(string $name, mixed $content): static
    {
        return $this->add($name, $content);
    }

    /**
     * @return string
     */
    public function render(): string
    {
        return view('admin.widgets.tab', ['tab' => $this])->render();
    }

    /**
     * @return array<string, mixed>
     */
    public function getTabs(): array
    {
        return $this->tabs;
    }
}
