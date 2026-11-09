<?php

namespace Encore\Admin\Controllers\Pages;

use Filament\Resources\Pages\CreateRecord as FilamentCreateRecord;
use Filament\Schemas\Schema;

/**
 * Generic create page for ported resources. Bridges the controller's
 * laravel-admin form() DSL; the original form()->saving() hooks run
 * through Form::process() before the record is created.
 */
class CreateRecord extends FilamentCreateRecord
{
    public function getTitle(): string
    {
        return static::getResource()::newController()->title();
    }

    public function getSubheading(): ?string
    {
        $description = static::getResource()::newController()->description('create');

        return $description ?? trans('admin.create');
    }

    public function form(Schema $schema): Schema
    {
        $form = static::getResource()::newController()->form();

        if (!$form instanceof \Encore\Admin\Form) {
            return $schema;
        }

        return $form->applyToSchema($schema, $schema->getOperation(), $schema->getRecord());
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return static::getResource()::newController()->form()->process($data);
    }

    public static function getNavigationLabel(): string
    {
        return static::getResource()::newController()->title();
    }
}
