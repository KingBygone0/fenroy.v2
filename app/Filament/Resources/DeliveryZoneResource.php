<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryZoneResource\Pages;
use App\Models\DeliveryZone;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DeliveryZoneResource extends Resource
{
    protected static ?string $model = DeliveryZone::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static string|\UnitEnum|null $navigationGroup = 'Commerce';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->placeholder('e.g. Osu · Labone · Cantonments')
                ->columnSpan(2),
            TagsInput::make('areas')
                ->label('Area Names (press Enter after each)')
                ->placeholder('e.g. Osu')
                ->required()
                ->columnSpan(2),
            TextInput::make('fee')
                ->numeric()
                ->prefix('GH₵')
                ->required()
                ->label('Delivery Fee')
                ->columnSpan(1),
            TextInput::make('free_above')
                ->numeric()
                ->prefix('GH₵')
                ->nullable()
                ->label('Free Delivery Above (leave blank = never free)')
                ->columnSpan(1),
            TextInput::make('sort_order')
                ->numeric()
                ->default(0)
                ->label('Sort Order')
                ->columnSpan(1),
            Toggle::make('is_active')->default(true)->columnSpan(1),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('sort_order')->label('#')->sortable(),
                TextColumn::make('name')->searchable()->weight('bold'),
                TextColumn::make('areas')
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state)
                    ->wrap(),
                TextColumn::make('fee')->prefix('GH₵ ')->sortable(),
                TextColumn::make('free_above')
                    ->prefix('GH₵ ')
                    ->placeholder('—')
                    ->label('Free Above'),
                IconColumn::make('is_active')->boolean()->label('Active'),
            ])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDeliveryZones::route('/'),
            'create' => Pages\CreateDeliveryZone::route('/create'),
            'edit'   => Pages\EditDeliveryZone::route('/{record}/edit'),
        ];
    }
}
