<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Facades\Admin;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

/**
 * Ported user setting page (original AuthController@getSetting/putSetting,
 * URI /admin/auth/setting).
 */
class SettingController extends Page
{
    /**
     * Original URI: /admin/auth/setting.
     *
     * {@inheritdoc}
     */
    protected static ?string $slug = 'auth/setting';

    /**
     * {@inheritdoc}
     */
    public static function canAccess(): bool
    {
        return Admin::user() !== null;
    }

    /**
     * {@inheritdoc}
     */
    public static function getNavigationLabel(): string
    {
        return trans('admin.user_setting');
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle(): string
    {
        return trans('admin.user_setting');
    }

    /**
     * {@inheritdoc}
     */
    public static function shouldRegisterNavigation(): bool
    {
        // Reached from the user menu, like the original top-right dropdown.
        return false;
    }

    /**
     * The original settingForm(): username display, name, avatar,
     * password + confirmation.
     *
     * {@inheritdoc}
     */
    public function form(Schema $schema): Schema
    {
        $user = Admin::user();

        return $schema->components([
            \Filament\Schemas\Components\Grid::make()->columns(2)->components([
                \Filament\Schemas\Components\Group::make([
                    TextEntry::make('username')
                        ->label(trans('admin.username'))
                        ->state($user->username),
                    TextInput::make('name')
                        ->label(trans('admin.name'))
                        ->default($user->name)
                        ->required(),
                    TextInput::make('password')
                        ->label(trans('admin.password'))
                        ->password()
                        ->revealable()
                        ->dehydrated(fn ($state) => filled($state)),
                    TextInput::make('password_confirmation')
                        ->label(trans('admin.password_confirmation'))
                        ->password()
                        ->revealable()
                        ->sameAs('password')
                        ->dehydrated(false),
                ])->columnSpan(1),
                \Filament\Schemas\Components\Group::make([
                    FileUpload::make('avatar')
                        ->label(trans('admin.avatar'))
                        ->image()
                        ->disk(config('admin.upload.disk'))
                        ->directory(config('admin.upload.directory.image'))
                        ->default($user->getRawOriginal('avatar')),
                ])->columnSpan(1),
            ]),
        ]);
    }

    /**
     * Original putSetting(): hash a changed password, save, flash success.
     *
     * {@inheritdoc}
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('admin.save'))
                ->action(function () {
                    $data = collect($this->form->getState())
                        ->only(['name', 'avatar', 'password'])
                        ->filter(fn ($value) => filled($value))
                        ->all();

                    if (isset($data['password'])) {
                        $data['password'] = Hash::make($data['password']);
                    }

                    Admin::user()->fill($data)->save();

                    Notification::make()->title(trans('admin.save_succeeded'))->success()->send();
                }),
        ];
    }
}
