<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static string|\UnitEnum|null $navigationGroup = 'Commerce';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->columnSpan(2),
            TextInput::make('slug')->required()->helperText('Auto-generated from name if left blank')->columnSpan(1),
            TextInput::make('sku')->columnSpan(1),
            TextInput::make('unit')->placeholder('e.g. 500g pack')->columnSpan(1),
            TextInput::make('type')->placeholder('e.g. Fruits')->columnSpan(1),
            Textarea::make('description')->columnSpan(2),
            Select::make('category')
                ->options(fn () => Category::orderBy('sort_order')->pluck('name', 'slug')->toArray())
                ->searchable()
                ->columnSpan(2),
            TextInput::make('price')->numeric()->prefix('GH₵')->required()->columnSpan(1),
            TextInput::make('old_price')->numeric()->prefix('GH₵')->columnSpan(1),
            TextInput::make('stock')->numeric()->required()->columnSpan(1),
            FileUpload::make('image')
                ->image()
                ->disk('public')
                ->directory('products')
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                ->maxSize(2048)
                ->imageEditor()
                ->imagePreviewHeight('120')
                ->columnSpan(2),
            Toggle::make('is_active')->default(true)->columnSpan(1),
            Toggle::make('is_featured')->columnSpan(1),
            Toggle::make('is_best_seller')->columnSpan(1),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->disk('public')->square()->size(48),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('sku'),
                TextColumn::make('category'),
                TextColumn::make('price')->prefix('GH₵ ')->sortable(),
                TextColumn::make('stock')->sortable(),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('created_at')->since()->sortable(),
            ])
            ->actions([
                Action::make('adjustStock')
                    ->label('Adjust Stock')
                    ->icon('heroicon-m-arrow-path')
                    ->color('gray')
                    ->form([
                        TextInput::make('adjustment')
                            ->label('Adjustment (positive = add, negative = remove)')
                            ->numeric()
                            ->required()
                            ->helperText('e.g. 10 to add 10, -5 to remove 5'),
                        Textarea::make('reason')
                            ->label('Reason (optional)')
                            ->rows(2)
                            ->placeholder('e.g. Received new shipment'),
                    ])
                    ->action(function (Product $record, array $data) {
                        $adjusted = max(0, $record->stock + (int) $data['adjustment']);
                        $record->update(['stock' => $adjusted]);
                        Notification::make()
                            ->title("Stock updated to {$adjusted} units")
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
