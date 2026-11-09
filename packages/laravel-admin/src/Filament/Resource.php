<?php

namespace Encore\Admin\Filament;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Controllers\Pages\CreateRecord;
use Encore\Admin\Controllers\Pages\EditRecord;
use Encore\Admin\Controllers\Pages\ListRecords;
use Encore\Admin\Controllers\Pages\ViewRecord;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Support\IconResolver;
use Filament\Resources\Resource as FilamentResource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Filament resource shim for a laravel-admin DSL controller.
 *
 * The laravel-admin controllers stay PLAIN classes with grid()/detail()/
 * form() (original style, no Filament inheritance — Filament's static
 * form() would collide with the DSL instance form()). This shim bridges:
 *
 *   table()      → controller->grid()
 *   infolist()   → controller->detail()
 *   (forms)      → controller->form()   (bridged on the generic pages)
 *
 * Subclasses only declare {@see Resource::controller()}.
 */
abstract class Resource extends FilamentResource
{
    /**
     * The laravel-admin controller class this shim serves.
     *
     * @return class-string<AdminController>
     */
    abstract public static function controller(): string;

    /**
     * Resolve the model class from the controller's grid builder
     * (grid() builds DSL definitions only — no queries).
     *
     * {@inheritdoc}
     */
    public static function getModel(): string
    {
        $grid = static::newController()->grid();

        if (!$grid instanceof Grid || !$grid->getModel()) {
            throw new \LogicException('Cannot resolve the model from the grid builder of '.static::controller());
        }

        return $grid->getModel()::class;
    }

    /**
     * The controller instance.
     *
     * @return AdminController
     */
    public static function newController(): AdminController
    {
        return new (static::controller());
    }

    /**
     * {@inheritdoc}
     */
    public static function table(Table $table): Table
    {
        $grid = static::newController()->grid();

        if (!$grid instanceof Grid) {
            return $table;
        }

        return $grid->setResource(static::class)->applyToTable($table);
    }

    /**
     * Show builder rendered as infolist.
     *
     * {@inheritdoc}
     */
    public static function infolist(Schema $schema): Schema
    {
        $record = $schema->getRecord();

        if (!$record) {
            return $schema;
        }

        $show = static::newController()->detail($record->getKey());

        if (!$show instanceof Show) {
            return $schema;
        }

        return $show->applyToSchema($schema);
    }

    /**
     * Filament requires one Page subclass per resource (the page carries a
     * typed `static $resource` property). To keep the laravel-admin
     * one-controller-per-page layout, the shim generates the four bound
     * page classes once per shim at registration time.
     *
     * {@inheritdoc}
     */
    public static function getPages(): array
    {
        $pages = [];

        foreach (static::pageDefinitions() as $key => [$path, $base]) {
            $pages[$key] = static::generatePageClass($key, $base)::route($path);
        }

        return $pages;
    }

    /**
     * Page definitions: key => [route path, generic page base class].
     * Override to register fewer pages (e.g. logs: index only).
     *
     * @return array<string, array{0: string, 1: class-string}>
     */
    protected static function pageDefinitions(): array
    {
        return [
            'index' => ['/', ListRecords::class],
            'create' => ['/create', CreateRecord::class],
            'edit' => ['/{record}/edit', EditRecord::class],
            'view' => ['/{record}', ViewRecord::class],
        ];
    }

    /**
     * Generate (once) and return the page class bound to this shim.
     *
     * @param string $key
     * @param string $base
     *
     * @return string
     */
    protected static function generatePageClass(string $key, string $base): string
    {
        $short = ucfirst($key).'Page';

        $namespace = static::class.'\\Pages';
        $class = $namespace.'\\'.$short;

        if (!class_exists($class, false)) {
            $code = sprintf(
                'namespace %s; class %s extends \%s { protected static string $resource = \%s::class; }',
                $namespace,
                $short,
                $base,
                static::class,
            );

            eval($code);
        }

        return $class;
    }

    /**
     * {@inheritdoc}
     */
    public static function getNavigationLabel(): string
    {
        return static::newController()->title();
    }

    /**
     * {@inheritdoc}
     */
    public static function getModelLabel(): string
    {
        return static::newController()->title();
    }

    /**
     * {@inheritdoc}
     */
    public static function getPluralModelLabel(): string
    {
        return static::newController()->title();
    }

    /**
     * {@inheritdoc}
     */
    public static function getNavigationIcon(): ?string
    {
        $menuModel = config('admin.database.menu_model');

        $icon = $menuModel::query()
            ->where('uri', 'like', static::adminUri().'%')
            ->orderBy('order')
            ->value('icon');

        return IconResolver::resolve($icon);
    }

    /**
     * The admin URI of this resource (original route path, including the
     * cluster prefix).
     *
     * @return string
     */
    public static function adminUri(): string
    {
        $cluster = static::getCluster();

        $slug = static::getSlug();

        return trim(($cluster ? $cluster::getSlug().'/' : '').$slug, '/');
    }

    /**
     * Port note: route-permission bridge (original admin.permission
     * middleware semantics) — see AdminController history. Livewire actions
     * ride on /livewire/update, so the semantic path is matched from the
     * resource instead of the request.
     *
     * {@inheritdoc}
     */
    public static function canAccess(): bool
    {
        if (config('admin.check_route_permission') === false) {
            return true;
        }

        $user = \Encore\Admin\Facades\Admin::user();

        if (!$user) {
            return false;
        }

        if ($user->isAdministrator()) {
            return true;
        }

        $request = Request::create(admin_base_path(static::adminUri()), 'GET');

        return (bool) $user->allPermissions()->first(function ($permission) use ($request) {
            return $permission->shouldPassThrough($request);
        });
    }

    /**
     * Slug note: the Filament default (`UserResource` → `users`) already
     * matches the original laravel-admin URIs (auth/users, auth/roles, …),
     * so no slug override is needed.
     *
     * Sidebar note: navigation entries are built from the admin_menu table
     * (MenuProvider) — resources must not register their own top-level
     * items, or the sidebar shows duplicates.
     *
     * {@inheritdoc}
     */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
