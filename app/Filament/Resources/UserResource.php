<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) User::count();
    }

    public static function form(Schema $schema): Schema
    {
        $isCreating = $schema->getLivewire() instanceof CreateRecord;

        return $schema->components([
            TextInput::make('name')->required()->columnSpan(1),
            TextInput::make('email')->email()->required()->unique(ignoreRecord: true)->columnSpan(1),
            TextInput::make('phone')->tel()->columnSpan(1),
            TextInput::make('password')
                ->password()
                ->revealable()
                ->required($isCreating)
                ->dehydrated(fn ($state) => filled($state))
                ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                ->helperText($isCreating ? 'Set a temporary password for the account.' : 'Leave blank to keep current password.')
                ->columnSpan(1),
            Toggle::make('is_admin')->label('Admin access')->helperText('Grants access to the full admin panel')->columnSpan(1),
            Toggle::make('is_staff')->label('POS access')->helperText('Grants access to the POS terminal only (fenroy.shop/pos)')->columnSpan(1),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable()->copyable(),
                TextColumn::make('phone')->placeholder('—'),
                IconColumn::make('is_admin')->boolean()->label('Admin'),
                IconColumn::make('is_staff')->boolean()->label('POS Staff'),
                TextColumn::make('created_at')->since()->sortable()->label('Joined'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view'   => Pages\ViewUser::route('/{record}'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
