<?php

namespace Encore\Admin\Controllers;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

/**
 * Ported auth controller.
 *
 * The original controller implemented getLogin/postLogin/getLogout/
 * getSetting/putSetting over plain HTTP routes. In the port the login form
 * rides on the Filament panel login page (username + password + remember
 * me, matching the original form), and logout is the Filament panel's
 * native logout. The setting page is attached in M2.
 */
class AuthController extends BaseLogin
{
    public function form(Schema $schema): Schema
    {
        return $schema->components(array_filter([
            $this->getUsernameFormComponent(),
            $this->getPasswordFormComponent(),
            config('admin.auth.remember', true) ? $this->getRememberFormComponent() : null,
        ]));
    }

    protected function getUsernameFormComponent(): Component
    {
        return TextInput::make('username')
            ->label(trans('admin.username'))
            ->required()
            ->autocomplete('username')
            ->autofocus();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(#[\SensitiveParameter] array $data): array
    {
        return [
            'username' => $data['username'],
            'password' => $data['password'],
        ];
    }
}
