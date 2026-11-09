<?php

namespace Encore\Admin\Controllers\Pages;

use Encore\Admin\Grid;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords as FilamentListRecords;

/**
 * Generic list page for ported resources.
 */
class ListRecords extends FilamentListRecords
{
    public function getTitle(): string
    {
        return static::getResource()::newController()->title();
    }

    public function getSubheading(): ?string
    {
        $description = static::getResource()::newController()->description('index');

        return $description ?? trans('admin.list');
    }

    protected function getHeaderActions(): array
    {
        $grid = static::getResource()::newController()->grid();

        $actions = [];

        if ($grid instanceof Grid && !$grid->isCreateDisabled() && static::getResource()::hasPage('create')) {
            $actions[] = CreateAction::make();
        }

        return $actions;
    }

    public static function getNavigationLabel(): string
    {
        return static::getResource()::newController()->title();
    }
}
