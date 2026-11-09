<?php

namespace Encore\Admin\Widgets;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\HtmlString;

/**
 * Class Widget.
 *
 * Base widget: renders to an HTML string (embedded in Filament page
 * schemas via the Html component).
 */
abstract class Widget implements Htmlable
{
    /**
     * @return ViewContract|string
     */
    abstract public function render();

    /**
     * @return string
     */
    public function html(): string
    {
        $rendered = $this->render();

        if ($rendered instanceof ViewContract) {
            return $rendered->render();
        }

        return (string) $rendered;
    }

    /**
     * @return HtmlString
     */
    public function toHtml(): HtmlString
    {
        return new HtmlString($this->html());
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->html();
    }
}
