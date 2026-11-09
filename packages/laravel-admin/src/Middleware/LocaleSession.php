<?php

namespace Encore\Admin\Middleware;

use Encore\Admin\Support\Locales;
use Closure;
use Illuminate\Http\Request;

/**
 * Applies the session-selected locale on every panel request (the default
 * remains APP_LOCALE from the environment).
 */
class LocaleSession
{
    public function handle(Request $request, Closure $next)
    {
        if (config('admin.locale.enable_switch', true)) {
            $locale = session('admin.locale');

            if (Locales::isValid($locale)) {
                app()->setLocale($locale);
            }
        }

        return $next($request);
    }
}
