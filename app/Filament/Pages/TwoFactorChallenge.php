<?php

namespace App\Filament\Pages;

use App\Services\TotpService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Crypt;
use Livewire\Attributes\Rule;

class TwoFactorChallenge extends Page
{
    protected static string $routePath = 'two-factor-challenge';

    protected string $view = 'filament.pages.two-factor-challenge';

    protected static bool $shouldRegisterNavigation = false;

    #[Rule('required|digits:6')]
    public string $code = '';

    public function verify(): void
    {
        $this->validate();

        $user = auth()->user();
        if (! $user?->two_factor_secret) {
            session(['two_factor_verified' => true]);
            $this->redirect(filament()->getUrl());
            return;
        }

        $secret = Crypt::decryptString($user->two_factor_secret);

        if (! app(TotpService::class)->verify($secret, $this->code)) {
            $this->addError('code', 'Invalid code. Please try again.');
            return;
        }

        session(['two_factor_verified' => true]);
        $this->redirect(filament()->getUrl());
    }

    public function getTitle(): string
    {
        return 'Two-Factor Verification';
    }
}
