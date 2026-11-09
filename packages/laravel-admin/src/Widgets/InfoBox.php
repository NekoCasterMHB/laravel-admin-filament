<?php

namespace Encore\Admin\Widgets;

/**
 * Class InfoBox.
 */
class InfoBox extends Widget
{
    /**
     * @var string
     */
    protected string $text;

    /**
     * @var string
     */
    protected string $icon;

    /**
     * @var string
     */
    protected string $info;

    /**
     * @var string
     */
    protected string $topic;

    /**
     * @var string
     */
    protected string $style = 'info';

    /**
     * InfoBox constructor.
     *
     * @param string $name
     * @param string $icon
     * @param string $info
     * @param string $topic
     * @param string $style
     */
    public function __construct(string $name, string $icon, string $info, string $topic, string $style = 'info')
    {
        $this->text = $name;
        $this->icon = $icon;
        $this->info = $info;
        $this->topic = $topic;
        $this->style = $style;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        return view('admin.widgets.info-box', ['box' => $this])->render();
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @return string
     */
    public function getInfo(): string
    {
        return $this->info;
    }

    /**
     * @return string
     */
    public function getTopic(): string
    {
        return $this->topic;
    }

    /**
     * @return string
     */
    public function getStyle(): string
    {
        return $this->style;
    }
}
