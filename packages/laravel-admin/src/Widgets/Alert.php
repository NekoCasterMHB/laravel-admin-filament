<?php

namespace Encore\Admin\Widgets;

/**
 * Class Alert.
 */
class Alert extends Widget
{
    /**
     * @var string
     */
    protected string $title;

    /**
     * @var string
     */
    protected string $content;

    /**
     * @var string
     */
    protected string $style = 'info';

    /**
     * @var string|null
     */
    protected ?string $icon = null;

    /**
     * Alert constructor.
     *
     * @param string      $content
     * @param string      $title
     * @param string      $style
     * @param string|null $icon
     */
    public function __construct(string $content = '', string $title = '', string $style = 'info', ?string $icon = null)
    {
        $this->content = $content;
        $this->title = $title;
        $this->style = $style;
        $this->icon = $icon;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        return view('admin.widgets.alert', ['alert' => $this])->render();
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getStyle(): string
    {
        return $this->style;
    }

    /**
     * @return string|null
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }
}
