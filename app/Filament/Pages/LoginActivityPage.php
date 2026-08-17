<?php

namespace App\Filament\Pages;

use App\Models\LoginActivity;
use Filament\Pages\Page;
use Livewire\Attributes\Computed;

class LoginActivityPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Login Activity';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.login-activity';

    #[Computed]
    public function activities(): \Illuminate\Database\Eloquent\Collection
    {
        return LoginActivity::with('user')
            ->latest('created_at')
            ->limit(200)
            ->get();
    }
}
