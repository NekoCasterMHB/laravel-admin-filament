<?php

namespace Encore\Admin\Widgets;

/**
 * Class Callout.
 */
class Callout extends Widget
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
     * Callout constructor.
     *
     * @param string $title
     * @param string $content
     * @param string $style info|warning|danger|success
     */
    public function __construct(string $title = '', string $content = '', string $style = 'info')
    {
        $this->title = $title;
        $this->content = $content;
        $this->style = $style;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        return view('admin.widgets.callout', ['callout' => $this])->render();
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
}
