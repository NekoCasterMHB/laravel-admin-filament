<?php

namespace Encore\Admin\Support;

use Illuminate\Support\Str;

/**
 * Maps legacy Font Awesome 4 icon names (stored in admin_menu.icon by the
 * original seeder) to Font Awesome 6/7 names provided by the installed
 * blade-fontawesome package.
 */
class IconResolver
{
    /**
     * Legacy FA4 name (without the fa- prefix) → FA6/7 name (without prefix).
     *
     * @var array
     */
    protected const MAP = [
        'bar-chart' => 'chart-column',
        'area-chart' => 'chart-area',
        'line-chart' => 'chart-line',
        'pie-chart' => 'chart-pie',
        'tasks' => 'list-check',
        'history' => 'clock-rotate-left',
        'gear' => 'gear',
        'cogs' => 'gears',
        'dashboard' => 'gauge-high',
        'home' => 'house',
        'edit' => 'pen-to-square',
        'trash' => 'trash-can',
        'save' => 'floppy-disk',
        'search' => 'magnifying-glass',
        'sign-out' => 'right-from-bracket',
        'exchange' => 'right-left',
        'clock-o' => 'clock',
        'money' => 'money-bill',
        'shopping-cart' => 'cart-shopping',
        'th-large' => 'table-cells-large',
        'th-list' => 'table-list',
        'question-circle' => 'circle-question',
        'info-circle' => 'circle-info',
        'check-circle' => 'circle-check',
        'times-circle' => 'circle-xmark',
        'exclamation-circle' => 'circle-exclamation',
        'arrow-circle-o-right' => 'circle-right',
        'circle-o' => 'circle',
    ];

    /**
     * Resolve a stored icon name (e.g. "fa-bar-chart") to a blade-icons
     * name usable by Filament (e.g. "fas-chart-column").
     *
     * @param string|null $icon
     * @param string      $fallback
     *
     * @return string
     */
    public static function resolve(?string $icon, string $fallback = 'fas-list'): string
    {
        if (blank($icon)) {
            return $fallback;
        }

        $name = Str::of(trim($icon))
            ->replaceFirst('fa-', '')
            ->replaceFirst('fas-', '')
            ->toString();

        return 'fas-'.(static::MAP[$name] ?? $name);
    }
}
