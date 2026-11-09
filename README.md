# Laravel Admin Filament

[![PHP](https://img.shields.io/badge/PHP-%5E8.3-777bb4)](https://www.php.net)
[![Laravel](https://img.shields.io/badge/Laravel-%5E13-ff2d20)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-%5E5.11-f59e0b)](https://filamentphp.com)
[![License](https://img.shields.io/badge/license-MIT-blue)](LICENSE)

A modern port of [z-song/laravel-admin](https://github.com/z-song/laravel-admin) (the dormant
`encore/laravel-admin` 1.8.x) on top of **Filament 5** and current **Laravel 13 / PHP 8.5**.

It keeps what made laravel-admin productive:

- the same `Encore\Admin\` DSL — `new Grid($model)`, `$grid->column(...)`, `new Form($model)`,
  `$form->text(...)`, `$show->field(...)`, `Content / Row / Column`, Widgets, Tree;
- the same file layout — `app/Admin/{bootstrap.php,routes.php,Controllers}`, `config/admin.php`,
  `lang/`, `packages/laravel-admin/src/`;
- the same database — exactly the nine `admin_*` tables from the original migration, column for column;
- the same pages — login (username), dashboard, users / roles / permissions / menu / operation logs,
  user settings, RBAC with path + HTTP-method permissions, operation logging, CSV export.

…while rendering through Filament (Livewire 4) instead of AdminLTE/jQuery, and adding a topbar
language switcher (session-based, `APP_LOCALE` as default).

## Requirements

- PHP **8.3+** (8.5 recommended)
- Composer 2
- MySQL / PostgreSQL / SQLite

## Installation

```bash
composer create-project nekocastermhb/laravel-admin-filament my-admin   # or clone this repo
cd my-admin

cp .env.example .env
php artisan key:generate
touch database/database.sqlite          # SQLite for local development; configure MySQL if you prefer

php artisan admin:install               # migrations + seed (admin / admin)
php artisan filament:assets             # publish front-end assets
php artisan serve                       # http://127.0.0.1:8000/admin
```

Log in with **admin / admin** and change the password right away (avatar menu → User setting).

Runtime language switching: set `APP_LOCALE` for the default and pick another language from the
globe icon in the topbar (en / 简体中文 / 日本語 shipped; 23 locales included).

## Writing a page (same experience as the original)

1. Write a DSL controller (see `packages/laravel-admin/src/Controllers/UserController.php`):

   ```php
   class PostController extends AdminController
   {
       protected $title = 'Posts';

       public function grid()
       {
           $grid = new Grid(new Post());
           $grid->column('id', 'ID')->sortable();
           $grid->column('title');
           $grid->column('created_at');
           return $grid;
       }

       public function form()  { /* new Form(new Post()) ... */ }
       public function detail($id) { /* new Show(Post::findOrFail($id)) ... */ }
   }
   ```

   Builder methods must be `public` (the Filament bridge calls them).

2. Add a 10-line shim declaring `controller()`:

   ```php
   class PostResource extends \Encore\Admin\Filament\Resource
   {
       public static function controller(): string { return PostController::class; }
   }
   ```

3. Register it in `app/Admin/routes.php`:

   ```php
   Admin::resource(\Encore\Admin\Filament\Resources\PostResource::class);
   // custom page: Admin::page(MyPage::class);
   ```

That is it — list / create / edit / view / delete / export / permissions / operation log come for free,
and the sidebar entry follows the `admin_menu` table (menu admin page).

## Project structure

```
app/Admin/                    application skeleton (bootstrap / routes / controllers)
config/admin.php              configuration (keys identical to the original)
packages/laravel-admin/src/   the Encore\Admin\ core (Grid, Form, Show, Layout,
                              Widgets, Tree, Middleware, Console, Auth, clusters)
database/migrations/          the original 2016_01_04_173148 migration, unchanged
lang/                         23 locales
docs/                         port mapping, differences, page checklist
tests/                        schema parity, pages, RBAC, menu tree, save pipeline
```

## Console commands

```
php artisan admin:install          # migrate + seed the admin tables
php artisan admin:create-user      # create another administrator
php artisan admin:reset-password   # reset a password
php artisan admin:menu             # print the current sidebar tree
```

## Documentation

- [docs/port-mapping.md](docs/port-mapping.md) — DSL → Filament mapping (Grid / Form / Show / Layout)
- [docs/differences.md](docs/differences.md) — intentional differences from the original
- [docs/page-checklist.md](docs/page-checklist.md) — page-by-page feature checklist

## Credits & License

This project is a port of [laravel-admin](https://github.com/z-song/laravel-admin) by **z-song**
(MIT). The original DSL, database schema, seed data and page behaviour are preserved; the rendering
layer is [Filament](https://filamentphp.com). Menu tree UI uses
[solution-forest/filament-tree](https://github.com/solutionforest/filament-tree).

Released under the [MIT license](LICENSE).
