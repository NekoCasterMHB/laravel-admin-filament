<?php

namespace Encore\Admin\Filament\Resources;

use Encore\Admin\Controllers\PermissionController;
use Encore\Admin\Filament\Resource;

class PermissionResource extends Resource
{
    protected static ?string $cluster = \Encore\Admin\Clusters\Auth::class;

    public static function controller(): string
    {
        return PermissionController::class;
    }
}
