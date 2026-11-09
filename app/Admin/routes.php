<?php

use Encore\Admin\Facades\Admin;

/*
|--------------------------------------------------------------------------
| Admin routes
|--------------------------------------------------------------------------
|
| Original laravel-admin declared HTTP routes here:
|
|   Admin::routes();
|   Route::group([...prefix/namespace/middleware...], function (Router $router) {
|       $router->get('/', 'HomeController@index')->name('home');
|   });
|
| The Filament port serves the same pages through the panel, so this file
| is now a declarative registry: Admin::routes() pulls in the laravel-admin
| builtins, Admin::page()/Admin::resource() register the app pages that the
| original $router->... lines declared.
|
*/

Admin::routes();

// $router->get('/', 'HomeController@index')->name('home');
Admin::page(\App\Admin\Controllers\HomeController::class);
