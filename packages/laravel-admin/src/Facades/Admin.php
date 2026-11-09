<?php

namespace Encore\Admin\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Admin.
 *
 * @method static \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard guard()
 * @method static \Illuminate\Contracts\Auth\Authenticatable|null user()
 * @method static array menu()
 * @method static string title()
 */
class Admin extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Encore\Admin\Admin::class;
    }
}
