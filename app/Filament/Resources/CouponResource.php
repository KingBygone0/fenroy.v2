<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Actions\BulkActionGroup;
use Illuminate\Support\Facades\DB;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    protected static string|\UnitEnum|null $navigationGroup = 'Commerce';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')
                ->required()
                ->unique(ignoreRecord: true)
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($set, $state) => $set('code', strtoupper((string) $state)))
                ->columnSpan(1),
            Select::make('type')
                ->options(['fixed' => 'Fixed Amount (GH₵)', 'percent' => 'Percentage (%)'])
                ->required()
                ->columnSpan(1),
            TextInput::make('value')
                ->numeric()
                ->required()
                ->helperText('GH₵ amount for fixed, or number like 15 for 15%')
                ->columnSpan(1),
            TextInput::make('min_order')
                ->numeric()
                ->default(0)
                ->prefix('GH₵')
                ->label('Minimum Order')
                ->columnSpan(1),
            TextInput::make('max_uses')
                ->numeric()
                ->nullable()
                ->label('Max Uses (blank = unlimited)')
                ->columnSpan(1),
            DateTimePicker::make('expires_at')
                ->nullable()
                ->label('Expiry Date & Time')
                ->columnSpan(1),
            Toggle::make('is_active')->default(true)->columnSpan(2),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->searchable()->copyable()->weight('bold'),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn ($state) => $state === 'percent' ? 'info' : 'success')
                    ->formatStateUsing(fn ($state) => $state === 'percent' ? 'Percent' : 'Fixed'),
                TextColumn::make('value')
                    ->formatStateUsing(fn ($state, $record) =>
                        $record->type === 'percent'
                            ? $state . '%'
                            : 'GH₵ ' . number_format($state, 2)
                    ),
                TextColumn::make('min_order')->prefix('GH₵ ')->label('Min Order'),
                TextColumn::make('used_count')
                    ->label('Used / Limit')
                    ->formatStateUsing(fn ($state, $record) =>
                        $state . ($record->max_uses ? ' / ' . $record->max_uses : ' / ∞')
                    ),
                TextColumn::make('total_discount')
                    ->label('Total Discount Given')
                    ->getStateUsing(function ($record) {
                        return DB::table('orders')
                            ->where('coupon_code', $record->code)
                            ->where('payment_status', 'paid')
                            ->sum('discount');
                    })
                    ->formatStateUsing(fn ($state) => 'GH₵ ' . number_format((float) $state, 2))
                    ->sortable(false),
                TextColumn::make('expires_at')->since()->placeholder('No expiry'),
                IconColumn::make('is_active')->boolean()->label('Active'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit'   => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
