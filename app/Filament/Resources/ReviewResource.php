<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static string|\UnitEnum|null $navigationGroup = 'Commerce';

    protected static ?int $navigationSort = 7;

    public static function getNavigationBadge(): ?string
    {
        $pending = Review::whereNull('is_approved')->count();
        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('reviewer_name')->disabled()->columnSpan(1),
            TextInput::make('rating')->disabled()->columnSpan(1),
            TextInput::make('product.name')->label('Product')->disabled()->columnSpan(2),
            Textarea::make('body')->disabled()->rows(4)->columnSpan(2),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('product.name')->label('Product')->searchable()->sortable()->limit(30),
                TextColumn::make('reviewer_name')->searchable(),
                TextColumn::make('rating')
                    ->formatStateUsing(fn ($state) => str_repeat('★', $state) . str_repeat('☆', 5 - $state))
                    ->html(),
                TextColumn::make('body')->limit(60)->wrap(),
                TextColumn::make('is_approved')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        true    => 'success',
                        false   => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        true    => 'Approved',
                        false   => 'Rejected',
                        default => 'Pending',
                    }),
                TextColumn::make('created_at')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('is_approved')
                    ->label('Status')
                    ->options([
                        '1' => 'Approved',
                        '0' => 'Rejected',
                    ])
                    ->placeholder('Pending'),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->requiresConfirmation(false)
                    ->action(function (Review $record) {
                        $record->update(['is_approved' => true]);
                        Notification::make()->title('Review approved')->success()->send();
                    })
                    ->visible(fn (Review $record) => $record->is_approved !== true),
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Review $record) {
                        $record->update(['is_approved' => false]);
                        Notification::make()->title('Review rejected')->warning()->send();
                    })
                    ->visible(fn (Review $record) => $record->is_approved !== false),
                DeleteAction::make(),
            ])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
        ];
    }
}
