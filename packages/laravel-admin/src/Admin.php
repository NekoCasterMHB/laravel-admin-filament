<?php

namespace Encore\Admin;

use Encore\Admin\Auth\Database\Menu;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

/**
 * Class Admin.
 */
class Admin
{
    /**
     * The Laravel admin version.
     *
     * @var string
     */
    const VERSION = '2.0.0';

    /**
     * @var string
     */
    public static $metaTitle;

    /**
     * @var string
     */
    public static $favicon;

    /**
     * @var array
     */
    public static $extensions = [];

    /**
     * Registered panel pages (the ported equivalent of the original route
     * table entries declared in app/Admin/routes.php).
     *
     * @var array<class-string>
     */
    public static array $pages = [];

    /**
     * Registered panel resources (Encore\Admin\Controllers\AdminController
     * subclasses providing grid()/form()/detail()).
     *
     * @var array<class-string>
     */
    public static array $resources = [];

    /**
     * @var []Closure
     */
    protected static $bootingCallbacks = [];

    /**
     * @var []Closure
     */
    protected static $bootedCallbacks = [];

    /**
     * Returns the long version of Laravel-admin.
     *
     * @return string The long application version
     */
    public static function getLongVersion()
    {
        return sprintf('Laravel-admin <comment>version</comment> <info>%s</info>', self::VERSION);
    }

    /**
     * Register the laravel-admin builtin pages.
     *
     * Port note: in the original this method registered the HTTP resource
     * routes for auth/users|roles|permissions|menu|logs plus the handle
     * endpoints. In the Filament port the same builtins register as panel
     * resources/pages; login/logout/setting are wired through
     * config('admin.auth.controller').
     *
     * @return void
     */
    public function routes()
    {
        static::$resources[] = Filament\Resources\UserResource::class;
        static::$resources[] = Filament\Resources\RoleResource::class;
        static::$resources[] = Filament\Resources\PermissionResource::class;
        static::$resources[] = Filament\Resources\LogResource::class;

        // Menu management: the tree page (original resource auth/menu).
        static::$pages[] = Controllers\MenuController::class;

        // User setting page (original AuthController@setting, /auth/setting).
        static::$pages[] = Controllers\SettingController::class;
    }

    /**
     * Register a panel page class (app/Admin/routes.php declaration).
     *
     * @param string $page
     *
     * @return void
     */
    public static function page(string $page)
    {
        static::$pages[] = $page;
    }

    /**
     * Registered panel page classes (Admin::page() declarations).
     *
     * @return array<class-string>
     */
    public static function registeredPages(): array
    {
        return static::$pages;
    }

    /**
     * Registered panel resource classes (Admin::resource() declarations).
     *
     * @return array<class-string>
     */
    public static function registeredResources(): array
    {
        return static::$resources;
    }

    /**
     * Register a panel resource controller class.
     *
     * @param string $resource
     *
     * @return void
     */
    public static function resource(string $resource)
    {
        static::$resources[] = $resource;
    }

    /**
     * @param $model
     *
     * @return mixed
     */
    public function getModel($model)
    {
        if ($model instanceof Model) {
            return $model;
        }

        if (is_string($model) && class_exists($model)) {
            return $this->getModel(new $model());
        }

        throw new InvalidArgumentException("$model is not a valid model");
    }

    /**
     * Left sider-bar menu.
     *
     * @return array
     */
    public function menu()
    {
        static $menu = null;

        if (!empty($menu)) {
            return $menu;
        }

        $menuClass = config('admin.database.menu_model');

        /** @var Menu $menuModel */
        $menuModel = new $menuClass();

        return $menu = $menuModel->toTree();
    }

    /**
     * @param array $menu
     *
     * @return array
     */
    public function menuLinks($menu = [])
    {
        if (empty($menu)) {
            $menu = $this->menu();
        }

        $links = [];

        foreach ($menu as $item) {
            if (!empty($item['children'])) {
                $links = array_merge($links, $this->menuLinks($item['children']));
            } else {
                $links[] = Arr::only($item, ['title', 'uri', 'icon']);
            }
        }

        return $links;
    }

    /**
     * Set admin title.
     *
     * @param string $title
     *
     * @return void
     */
    public static function setTitle($title)
    {
        self::$metaTitle = $title;
    }

    /**
     * Get admin title.
     *
     * @return string
     */
    public function title()
    {
        return self::$metaTitle ? self::$metaTitle : config('admin.title');
    }

    /**
     * @param null|string $favicon
     *
     * @return string|void
     */
    public static function favicon($favicon = null)
    {
        if (is_null($favicon)) {
            return static::$favicon;
        }

        static::$favicon = $favicon;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        return $this->guard()->user();
    }

    /**
     * Attempt to get the guard from the local cache.
     *
     * @return \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
     */
    public function guard()
    {
        $guard = config('admin.auth.guard') ?: 'admin';

        return Auth::guard($guard);
    }

    /**
     * Extend a extension.
     *
     * Port note: kept for API compatibility; the extension system is not
     * ported.
     *
     * @param string $name
     * @param string $class
     *
     * @return void
     */
    public static function extend($name, $class)
    {
        static::$extensions[$name] = $class;
    }

    /**
     * @param callable $callback
     */
    public static function booting(callable $callback)
    {
        static::$bootingCallbacks[] = $callback;
    }

    /**
     * @param callable $callback
     */
    public static function booted(callable $callback)
    {
        static::$bootedCallbacks[] = $callback;
    }

    /**
     * Bootstrap the admin application.
     */
    public function bootstrap()
    {
        $this->fireBootingCallbacks();

        require config('admin.bootstrap', admin_path('bootstrap.php'));

        $this->fireBootedCallbacks();
    }

    /**
     * Call the booting callbacks for the admin application.
     */
    protected function fireBootingCallbacks()
    {
        foreach (static::$bootingCallbacks as $callable) {
            call_user_func($callable);
        }
    }

    /**
     * Call the booted callbacks for the admin application.
     */
    protected function fireBootedCallbacks()
    {
        foreach (static::$bootedCallbacks as $callable) {
            call_user_func($callable);
        }
    }
}
