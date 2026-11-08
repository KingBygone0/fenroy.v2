<?php

namespace Tests\Feature;

use App\Support\NativeApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NativeAppTest extends TestCase
{
    use RefreshDatabase;

    private const APP_UA = 'Mozilla/5.0 (Linux; Android 16; wv) Chrome/144 Mobile Safari/537.36 FenroyApp/1.0';
    private const WEBVIEW_UA = 'Mozilla/5.0 (Linux; Android 16; wv) Chrome/144 Mobile Safari/537.36';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_browser_sees_the_apk_banner(): void
    {
        $this->withHeader('User-Agent', self::WEBVIEW_UA)
            ->get('/categories')
            ->assertOk()
            ->assertSee('Download for Android')
            ->assertCookieMissing(NativeApp::COOKIE);
    }

    public function test_app_request_hides_the_banner_and_sets_the_cookie(): void
    {
        $this->withHeader('User-Agent', self::APP_UA)
            ->get('/categories')
            ->assertOk()
            ->assertDontSee('Download for Android')
            ->assertCookie(NativeApp::COOKIE, '1');
    }

    /** Service-worker navigations on Android lose "FenroyApp" from the user agent but keep cookies. */
    public function test_cookie_alone_still_counts_as_the_app(): void
    {
        $this->withHeader('User-Agent', self::WEBVIEW_UA)
            ->withCookie(NativeApp::COOKIE, '1')
            ->get('/categories')
            ->assertOk()
            ->assertDontSee('Download for Android');
    }
}
