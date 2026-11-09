<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 */

// Port note: the Form engine lands with the M1 milestone; until then the
// original bootstrap call is guarded.
if (class_exists(\Encore\Admin\Form::class)) {
    \Encore\Admin\Form::forget(['map', 'editor']);
}
