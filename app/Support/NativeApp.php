<?php

namespace App\Support;

/** Detects requests coming from the Fenroy Android/iOS app (its WebView adds "FenroyApp" to the user agent). */
class NativeApp
{
    public static function detected(): bool
    {
        return str_contains(request()->userAgent() ?? '', 'FenroyApp');
    }
}
