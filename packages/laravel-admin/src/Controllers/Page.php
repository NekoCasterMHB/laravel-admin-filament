<?php

namespace Encore\Admin\Controllers;

use Filament\Pages\Page as FilamentPage;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Base class for every laravel-admin page in the port.
 *
 * Maps the original Content::title()/description() pair onto the Filament
 * page heading/subheading and provides the content() hook every ported
 * controller fills (the original `index(Content $content)` body).
 */
abstract class Page extends FilamentPage
{
    /**
     * Page title (the original Content::title()).
     *
     * @var string|null
     */
    protected static ?string $title = null;

    /**
     * Page description (the original Content::description()).
     *
     * @var string|Htmlable|null
     */
    protected static string|Htmlable|null $description = null;

    /**
     * Explicit route path — set it to '/' to make the page the panel home
     * (the original `$router->get('/', ...)`), or leave it null to derive
     * the path from the slug.
     *
     * @var string|null
     */
    protected static ?string $routePath = null;

    public static function getRoutePath(\Filament\Panel $panel): string
    {
        return static::$routePath ?? ('/'.static::getSlug($panel));
    }

    /**
     * Sidebar label (the original Content::title()).
     */
    public static function getNavigationLabel(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return static::$title ?? parent::getNavigationLabel();
    }

    /**
     * Sidebar note: navigation entries are built from the admin_menu table
     * (MenuProvider) — pages must not register their own top-level items.
     *
     * {@inheritdoc}
     */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    /**
     * Whether this page is the panel home (routePath '/'), i.e. the DB
     * menu's "Dashboard" node is served natively by it.
     */
    public static function isPanelHome(): bool
    {
        return static::$routePath === '/';
    }

    public function getHeading(): string|Htmlable|null
    {
        return $this->heading ?? static::$title ?? parent::getTitle();
    }

    public function getSubheading(): string|Htmlable|null
    {
        return $this->subheading ?? static::$description;
    }

    public function content(Schema $schema): Schema
    {
        return $schema;
    }
}
