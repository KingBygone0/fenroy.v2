<?php

namespace App\Support;

use Illuminate\Support\Facades\Cookie;

/**
 * Detects requests coming from the Fenroy Android/iOS app (its WebView adds "FenroyApp" to the user agent).
 *
 * The site's service worker (public/sw.js) fetches page navigations itself, and on Android those
 * requests carry the WebView's default user agent without "FenroyApp". So the first app request
 * also sets a cookie, which the service worker's requests do carry.
 */
class NativeApp
{
    public const COOKIE = 'fenroy_app';

    public static function detected(): bool
    {
        if (str_contains(request()->userAgent() ?? '', 'FenroyApp')) {
            if (request()->cookie(self::COOKIE) !== '1') {
                Cookie::queue(self::COOKIE, '1', 60 * 24 * 365);
            }

            return true;
        }

        return request()->cookie(self::COOKIE) === '1';
    }
}
