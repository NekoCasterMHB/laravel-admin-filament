<?php

namespace Encore\Admin\Controllers\Pages;

use Filament\Resources\Pages\ViewRecord as FilamentViewRecord;

/**
 * Generic view (detail) page for ported resources — renders the Show
 * builder as an infolist (bridged on the shim's infolist()).
 */
class ViewRecord extends FilamentViewRecord
{
    public function getTitle(): string
    {
        return static::getResource()::newController()->title();
    }

    public function getSubheading(): ?string
    {
        $description = static::getResource()::newController()->description('show');

        return $description ?? trans('admin.show');
    }

    public static function getNavigationLabel(): string
    {
        return static::getResource()::newController()->title();
    }
}
