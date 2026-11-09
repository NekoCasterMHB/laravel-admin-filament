<?php

namespace Encore\Admin\Filament\Resources;

use Encore\Admin\Controllers\RoleController;
use Encore\Admin\Filament\Resource;

class RoleResource extends Resource
{
    protected static ?string $cluster = \Encore\Admin\Clusters\Auth::class;

    public static function controller(): string
    {
        return RoleController::class;
    }
}
