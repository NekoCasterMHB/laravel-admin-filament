<?php

namespace Encore\Admin\Filament\Resources;

use Encore\Admin\Controllers\LogController;
use Encore\Admin\Filament\Resource;
use Encore\Admin\Controllers\Pages\ListRecords;

/**
 * Original route registration allowed only index + destroy for logs; the
 * port keeps only the list page (deletion is a row/bulk action).
 */
class LogResource extends Resource
{
    protected static ?string $cluster = \Encore\Admin\Clusters\Auth::class;

    public static function controller(): string
    {
        return LogController::class;
    }

    /**
     * Original route registration allowed only index + destroy for logs.
     *
     * {@inheritdoc}
     */
    protected static function pageDefinitions(): array
    {
        return [
            'index' => ['/', ListRecords::class],
        ];
    }
}
