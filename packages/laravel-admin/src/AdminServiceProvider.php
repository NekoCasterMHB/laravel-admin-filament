<?php

namespace Encore\Admin;

use Encore\Admin\Middleware\Authenticate;
use Encore\Admin\Middleware\Bootstrap;
use Encore\Admin\Middleware\LogOperation;
use Encore\Admin\Middleware\LocaleSession;
use Encore\Admin\Middleware\Permission;
use Encore\Admin\Middleware\Pjax;
use Encore\Admin\Middleware\Session;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AdminServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $commands = [
        Console\InstallCommand::class,
        Console\CreateUserCommand::class,
        Console\ResetPasswordCommand::class,
        Console\MenuCommand::class,
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'admin.auth'       => Authenticate::class,
        'admin.pjax'       => Pjax::class,
        'admin.log'        => LogOperation::class,
        'admin.permission' => Permission::class,
        'admin.bootstrap'  => Bootstrap::class,
        'admin.session'    => Session::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'admin' => [
            'admin.auth',
            'admin.pjax',
            'admin.log',
            'admin.bootstrap',
            'admin.permission',
        ],
    ];

    /**
     * Register the service provider.
     *
     * Everything that feeds the Filament panel (the declarative routes.php
     * registry and the panel itself) runs in the register phase: package
     * providers boot before app providers, and Filament loads its panel
     * routes while booting — the panel must already be in the registry by
     * then (same timing contract as Filament\PanelProvider).
     *
     * @return void
     */
    public function register()
    {
        $this->loadAdminAuthConfig();

        $this->registerRouteMiddleware();

        // app/Admin/bootstrap.php — same contract as the original package.
        Facades\Admin::bootstrap();

        // app/Admin/routes.php — in the port this file is a declarative
        // registry: Admin::routes() + Admin::page()/Admin::resource().
        if (file_exists($routes = admin_path('routes.php'))) {
            require $routes;
        }

        $this->registerPanel();
    }

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'admin');

        $this->ensureHttps();

        // Topbar user menu (original AdminLTE dropdown): avatar image,
        // user setting, language sub-menu and sign out. It replaces
        // Filament's letter-avatar menu (hidden via admin.css), so the
        // language switcher now lives under "Language" there.
        \Filament\Support\Facades\FilamentView::registerRenderHook(
            \Filament\View\PanelsRenderHook::TOPBAR_END,
            fn () => view('admin::partials.user-menu')->render(),
        );

        // Runtime language switching endpoint (session-stored locale).
        if (\Encore\Admin\Support\Locales::switchable()) {
            app('router')->prefix(trim(config('admin.route.prefix'), '/'))
                ->middleware('web')
                ->name(config('admin.route.prefix').'.')
                ->group(function ($router) {
                    $router->get('locale/{locale}', Http\Controllers\LocaleController::class)
                        ->name('locale');
                });
        }

        // Self-contained stylesheet for the ported widgets/dashboard
        // (Filament's pre-compiled Tailwind build only ships classes used
        // by Filament's own UI, so custom classes would not apply).
        \Filament\Support\Facades\FilamentView::registerRenderHook(
            \Filament\View\PanelsRenderHook::HEAD_END,
            function () {
                $css = file_get_contents(__DIR__.'/../resources/css/admin.css');

                return '<style data-laravel-admin>'.$css.'</style>';
            },
        );


        // Sidebar toggle icons: Font Awesome indent/outdent (lines + arrow),
        // provided by owenvoke/blade-fontawesome.
        \Filament\Support\Facades\FilamentIcon::register([
            \Filament\View\PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON => 'fas-outdent',
            \Filament\View\PanelsIconAlias::SIDEBAR_EXPAND_BUTTON => 'fas-indent',
            \Filament\View\PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON_RTL => 'fas-indent',
            \Filament\View\PanelsIconAlias::SIDEBAR_EXPAND_BUTTON_RTL => 'fas-outdent',
            \Filament\View\PanelsIconAlias::TOPBAR_OPEN_SIDEBAR_BUTTON => \Filament\Support\Icons\Heroicon::OutlinedBars3,
            \Filament\View\PanelsIconAlias::TOPBAR_CLOSE_SIDEBAR_BUTTON => \Filament\Support\Icons\Heroicon::OutlinedBars3,
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
    }

    /**
     * Register the Filament panel that renders the admin.
     *
     * @return void
     */
    protected function registerPanel()
    {
        $prefix = trim(config('admin.route.prefix'), '/');

        Filament::registerPanel(
            Panel::make()
                ->default()
                ->id($prefix ?: 'admin')
                ->path($prefix)
                ->authGuard(config('admin.auth.guard'))
                // Original URI: /admin/auth/login (keeps the seeded
                // `auth.login` permission http_path valid).
                ->login(config('admin.auth.controller'))
                ->loginRouteSlug('auth/login')
                ->authMiddleware([\Filament\Http\Middleware\Authenticate::class])
                ->pages(array_values(array_unique(Admin::$pages)))
                ->resources(array_values(array_unique(Admin::$resources)))
                // Core clusters (the `auth` group reproducing /admin/auth/* URIs).
                ->discoverClusters(in: __DIR__.'/Clusters', for: 'Encore\Admin\Clusters')
                // Sidebar = the admin_menu tree, exactly like the original
                // AdminLTE sidebar (Dashboard + the "Admin" expandable
                // submenu + custom links). The builder is resolved lazily at
                // render time, when the admin tables exist (register/boot run
                // before migrations on a fresh install).
                ->navigation(function (\Filament\Navigation\NavigationBuilder $builder) {
                    $navigation = \Encore\Admin\Filament\MenuProvider::navigation();

                    return $builder
                        ->groups($navigation['groups'])
                        ->items($navigation['items']);
                })
                // The canonical Filament web stack (cookies / CSRF /
                // sessions / error sharing) plus the ported middleware:
                // session-locale applier (runtime language switching) and
                // operation log (the ported `admin.log` group member).
                ->middleware([
                    \Illuminate\Cookie\Middleware\EncryptCookies::class,
                    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                    \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
                    \Illuminate\Session\Middleware\StartSession::class,
                    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                    LocaleSession::class,
                    LogOperation::class,
                ])
                ->brandName(config('admin.name'))
                // Collapsible sidebar on desktop (the original AdminLTE
                // sidebar-mini layout): collapsed state shows icons only with
                // tooltips; the choice is remembered by Filament.
                ->sidebarCollapsibleOnDesktop()
        );
    }

    /**
     * Custom admin_menu entries not served natively (see MenuProvider).
     * Safe during early boot: if the admin tables do not exist yet
     * (e.g. first `artisan migrate`), custom items are simply skipped.
     *
     * @return array
     */
    protected function dbNavigationItems(): array
    {
        try {
            return \Encore\Admin\Filament\MenuProvider::items();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Force to set https scheme if https enabled.
     *
     * @return void
     */
    protected function ensureHttps()
    {
        $is_admin = Str::startsWith(request()->getRequestUri(), '/'.ltrim(config('admin.route.prefix'), '/'));
        if ((config('admin.https') || config('admin.secure')) && $is_admin) {
            url()->forceScheme('https');
            $this->app['request']->server->set('HTTPS', true);
        }
    }

    /**
     * Setup auth configuration.
     *
     * @return void
     */
    protected function loadAdminAuthConfig()
    {
        config(Arr::dot(config('admin.auth', []), 'auth.'));
    }

    /**
     * Register the route middleware.
     *
     * @return void
     */
    protected function registerRouteMiddleware()
    {
        // register route middleware.
        foreach ($this->routeMiddleware as $key => $middleware) {
            app('router')->aliasMiddleware($key, $middleware);
        }

        // register middleware group.
        foreach ($this->middlewareGroups as $key => $middleware) {
            app('router')->middlewareGroup($key, $middleware);
        }
    }
}
