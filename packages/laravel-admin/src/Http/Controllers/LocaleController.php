<?php

namespace Encore\Admin\Http\Controllers;

use Encore\Admin\Support\Locales;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Switches the session locale, then redirects back (topbar switcher links
 * point here: /admin/locale/{locale}).
 */
class LocaleController
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        if (Locales::isValid($locale)) {
            session(['admin.locale' => $locale]);
        }

        return redirect()->to(url()->previous() ?: admin_url(''));
    }
}
