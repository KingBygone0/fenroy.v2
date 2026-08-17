<?php

namespace App\Filament\Pages;

use App\Services\TotpService;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Crypt;

class TwoFactorSetup extends Page
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-lock-closed';

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Two-Factor Auth';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.two-factor-setup';


    public ?string $pendingSecret = null;
    public string  $code          = '';

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form->schema([
            TextInput::make('code')
                ->label('6-digit code from your authenticator app')
                ->required()
                ->numeric()
                ->minLength(6)
                ->maxLength(6),
        ]);
    }

    public function generateSecret(): void
    {
        $secret              = app(TotpService::class)->generateSecret();
        $this->pendingSecret = Crypt::encryptString($secret);
        session(['totp_pending_secret' => $this->pendingSecret]);
    }

    public function enable(): void
    {
        $pending = session('totp_pending_secret') ?? $this->pendingSecret;
        if (! $pending) {
            Notification::make()->title('Generate a secret first.')->danger()->send();
            return;
        }

        $secret = Crypt::decryptString($pending);
        $code   = preg_replace('/\s+/', '', (string) $this->code);

        if (! app(TotpService::class)->verify($secret, $code)) {
            Notification::make()->title('Invalid code — try again.')->danger()->send();
            return;
        }

        $user                      = auth()->user();
        $user->two_factor_secret   = Crypt::encryptString($secret);
        $user->two_factor_enabled_at = now();
        $user->save();

        session()->forget('totp_pending_secret');
        $this->pendingSecret = null;
        $this->code          = '';

        Notification::make()->title('Two-factor authentication enabled.')->success()->send();
        $this->redirect(static::getUrl());
    }

    public function disable(): void
    {
        $user                      = auth()->user();
        $user->two_factor_secret   = null;
        $user->two_factor_enabled_at = null;
        $user->save();

        Notification::make()->title('Two-factor authentication disabled.')->warning()->send();
        $this->redirect(static::getUrl());
    }

    public function getQrCodeUrl(): ?string
    {
        $pending = session('totp_pending_secret') ?? $this->pendingSecret;
        if (! $pending) return null;

        $secret = Crypt::decryptString($pending);
        return app(TotpService::class)->qrCodeUrl($secret, auth()->user()->email);
    }

    public function getPendingPlainSecret(): ?string
    {
        $pending = session('totp_pending_secret') ?? $this->pendingSecret;
        if (! $pending) return null;
        return Crypt::decryptString($pending);
    }

    public function isTwoFactorEnabled(): bool
    {
        return ! is_null(auth()->user()->two_factor_enabled_at);
    }
}
