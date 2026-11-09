<?php

namespace Encore\Admin\Widgets;

/**
 * Class Box.
 *
 * A titled card containing arbitrary content (original AdminLTE box,
 * rendered with Tailwind utility classes).
 */
class Box extends Widget
{
    /**
     * @var string
     */
    protected string $title;

    /**
     * @var mixed
     */
    protected mixed $content;

    /**
     * Box style: default|primary|success|info|warning|danger.
     *
     * @var string
     */
    protected string $style = 'default';

    /**
     * @var array
     */
    protected array $tools = [];

    /**
     * Box constructor.
     *
     * @param string $title
     * @param mixed  $content
     * @param string $style
     */
    public function __construct(string $title = '', mixed $content = '', string $style = 'default')
    {
        $this->title = $title;
        $this->content = $content;
        $this->style = $style;
    }

    /**
     * @param string $style
     *
     * @return $this
     */
    public function style(string $style): static
    {
        $this->style = $style;

        return $this;
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
        if ($this->content instanceof Widget) {
            return $this->content->html();
        }

        return (string) $this->content;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        return view('admin.widgets.box', ['box' => $this])->render();
    }
}
