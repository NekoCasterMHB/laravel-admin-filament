<?php

namespace Encore\Admin\Support;

/**
 * Runtime language switching support.
 *
 * The default locale comes from the environment (APP_LOCALE); a user can
 * override it per session via the topbar switcher (see
 * Middleware\LocaleSession and Http\Controllers\LocaleController).
 */
class Locales
{
    /**
     * Enabled locale codes (config('admin.locale.list')).
     *
     * @return array
     */
    public static function enabled(): array
    {
        $list = (array) config('admin.locale.list', ['en']);

        return array_values(array_filter($list, fn ($locale) => is_string($locale) && $locale !== ''));
    }

    /**
     * Whether the switcher feature is on (needs >1 locale).
     *
     * @return bool
     */
    public static function switchable(): bool
    {
        return (bool) config('admin.locale.enable_switch', true)
            && count(static::enabled()) > 1;
    }

    /**
     * Human label for a locale code (config('admin.locale.labels') or the
     * code itself).
     *
     * @param string $locale
     *
     * @return string
     */
    public static function label(string $locale): string
    {
        return config("admin.locale.labels.{$locale}", $locale);
    }

    /**
     * Whether a locale is valid for switching.
     *
     * @param string|null $locale
     *
     * @return bool
     */
    public static function isValid(?string $locale): bool
    {
        return $locale !== null && in_array($locale, static::enabled(), true);
    }
}
