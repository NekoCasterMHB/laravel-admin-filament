<?php

namespace Encore\Admin\Filament\Resources;

use Encore\Admin\Controllers\UserController;
use Encore\Admin\Filament\Resource;

class UserResource extends Resource
{
    protected static ?string $cluster = \Encore\Admin\Clusters\Auth::class;

    public static function controller(): string
    {
        return UserController::class;
    }
}
