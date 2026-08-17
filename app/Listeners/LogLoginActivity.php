<?php

namespace App\Listeners;

use App\Models\LoginActivity;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class LogLoginActivity
{
    public function __construct(private readonly Request $request) {}

    public function handleLogin(Login $event): void
    {
        LoginActivity::create([
            'user_id'    => $event->user->id,
            'email'      => $event->user->email,
            'ip'         => $this->request->ip(),
            'user_agent' => substr($this->request->userAgent() ?? '', 0, 500),
            'successful' => true,
        ]);
    }

    public function handleFailed(Failed $event): void
    {
        LoginActivity::create([
            'user_id'    => null,
            'email'      => $event->credentials['email'] ?? '',
            'ip'         => $this->request->ip(),
            'user_agent' => substr($this->request->userAgent() ?? '', 0, 500),
            'successful' => false,
        ]);
    }
}
