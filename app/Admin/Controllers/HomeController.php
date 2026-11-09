<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Controllers\Page;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Row;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;

class HomeController extends Page
{
    /**
     * Title for current page (the original Content::title()).
     *
     * @var string
     */
    protected static ?string $title = 'Dashboard';

    /**
     * Panel home route (the original `$router->get('/', 'HomeController@index')`).
     *
     * @var string
     */
    protected static ?string $routePath = '/';

    /**
     * Sidebar ordering (above everything, like Filament's own dashboard).
     *
     * {@inheritdoc}
     */
    public static function getNavigationSort(): int
    {
        return -2;
    }

    /**
     * {@inheritdoc}
     */
    public static function getNavigationIcon(): ?string
    {
        return \Encore\Admin\Support\IconResolver::resolve('fa-bar-chart');
    }

    /**
     * Original index body, kept verbatim (plus the modern stats row).
     *
     * @param Content $content
     *
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->title('Dashboard')
            ->row(Dashboard::title())
            ->row(Dashboard::stats())
            ->row(function (Row $row) {

                // Port note: the original also showed an "Extensions" box
                // listing the (dormant) laravel-admin extensions. Nothing from
                // that ecosystem is installed here, so the box is dropped and
                // the remaining two boxes share the row.
                $row->column(6, function (Column $column) {
                    $column->append(Dashboard::environment());
                });

                $row->column(6, function (Column $column) {
                    $column->append(Dashboard::dependencies());
                });
            });
    }

    /**
     * Render the original Content layout as the Filament page content.
     *
     * {@inheritdoc}
     */
    public function content(Schema $schema): Schema
    {
        $layout = $this->index(new Content());

        $this->heading = $layout->getTitle() ?? 'Dashboard';
        $this->subheading = $layout->getDescription();

        return $schema->components($layout->toFilamentComponents());
    }
}
