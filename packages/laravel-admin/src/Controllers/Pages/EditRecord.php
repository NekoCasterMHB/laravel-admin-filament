<?php

namespace Encore\Admin\Controllers\Pages;

use Filament\Resources\Pages\EditRecord as FilamentEditRecord;
use Filament\Schemas\Schema;

/**
 * Generic edit page for ported resources. Bridges the controller's
 * laravel-admin form() DSL; the original form()->saving() hooks run
 * through Form::process() before the record is saved.
 */
class EditRecord extends FilamentEditRecord
{
    public function getTitle(): string
    {
        return static::getResource()::newController()->title();
    }

    public function getSubheading(): ?string
    {
        $description = static::getResource()::newController()->description('edit');

        return $description ?? trans('admin.edit');
    }

    public function form(Schema $schema): Schema
    {
        $form = static::getResource()::newController()->form();

        if (!$form instanceof \Encore\Admin\Form) {
            return $schema;
        }

        return $form->applyToSchema($schema, $schema->getOperation(), $schema->getRecord());
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return static::getResource()::newController()->form()->process($data, $this->getRecord());
    }

    public static function getNavigationLabel(): string
    {
        return static::getResource()::newController()->title();
    }
}
