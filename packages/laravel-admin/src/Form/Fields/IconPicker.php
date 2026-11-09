<?php

namespace Encore\Admin\Form\Fields;

use Filament\Forms\Components\Field;

/**
 * Font Awesome icon picker (the original laravel-admin `icon` field):
 * shows the current icon, allows typing a name, and offers a searchable
 * grid of clickable icons with live preview.
 */
class IconPicker extends Field
{
    protected string $view = 'admin::form.fields.icon-picker';
}
