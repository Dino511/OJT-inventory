<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocaleController extends Controller
{
    private const SUPPORTED_LOCALES = ['en', 'tl'];

    public function set(Request $request, string $locale)
    {
        abort_unless(in_array($locale, self::SUPPORTED_LOCALES, true), 404);

        $request->session()->put('locale', $locale);

        return back();
    }
}
