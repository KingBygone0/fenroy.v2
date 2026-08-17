# Task 7 Brief: Wishlist & Address Admin Resources

## Context
Task 7 of 8. Laravel 13 / Filament v5 at C:\Users\King\Documents\LYNJAY\laravel\fenroy.

Existing models (already created, do NOT modify them):
- `App\Models\Wishlist` — fields: user_id, product_id. Relations: user() BelongsTo User, product() BelongsTo Product
- `App\Models\Address` — fields: user_id, full_name, phone, line1, city, region, is_default. Relation: user() BelongsTo User

Both resources are READ-ONLY — no create, edit, or delete for admin.

## File 1: Create `app/Filament/Resources/WishlistResource.php`

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WishlistResource\Pages;
use App\Models\User;
use App\Models\Wishlist;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WishlistResource extends Resource
{
    protected static ?string $model = Wishlist::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-heart';

    protected static string|\UnitEnum|null $navigationGroup = 'Commerce';

    protected static ?int $navigationSort = 8;

    protected static ?string $navigationLabel = 'Wishlists';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Added')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Customer')
                    ->options(User::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWishlists::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
```

## File 2: Create `app/Filament/Resources/WishlistResource/Pages/ListWishlists.php`

```php
<?php

namespace App\Filament\Resources\WishlistResource\Pages;

use App\Filament\Resources\WishlistResource;
use Filament\Resources\Pages\ListRecords;

class ListWishlists extends ListRecords
{
    protected static string $resource = WishlistResource::class;
}
```

## File 3: Create `app/Filament/Resources/AddressResource.php`

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AddressResource\Pages;
use App\Models\Address;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AddressResource extends Resource
{
    protected static ?string $model = Address::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static string|\UnitEnum|null $navigationGroup = 'Commerce';

    protected static ?int $navigationSort = 9;

    protected static ?string $navigationLabel = 'Addresses';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('full_name')->label('Name')->searchable(),
                TextColumn::make('line1')->label('Street'),
                TextColumn::make('city')->label('City'),
                TextColumn::make('region')->label('Region')->placeholder('—'),
                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Customer')
                    ->options(User::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAddresses::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
```

## File 4: Create `app/Filament/Resources/AddressResource/Pages/ListAddresses.php`

```php
<?php

namespace App\Filament\Resources\AddressResource\Pages;

use App\Filament\Resources\AddressResource;
use Filament\Resources\Pages\ListRecords;

class ListAddresses extends ListRecords
{
    protected static string $resource = AddressResource::class;
}
```

## Verify
```bash
php -l app/Filament/Resources/WishlistResource.php
php -l app/Filament/Resources/AddressResource.php
```

## Commit
```bash
git add app/Filament/Resources/WishlistResource.php app/Filament/Resources/WishlistResource/Pages/ListWishlists.php app/Filament/Resources/AddressResource.php app/Filament/Resources/AddressResource/Pages/ListAddresses.php
git commit -m "feat: add read-only Wishlist and Address admin resources"
```

## Report
Write to: `.superpowers/sdd/2026-08-14-admin-dashboard-completion/task-7-report.md`
Return: "Status: DONE, commit: [hash]" or "Status: BLOCKED, reason: [reason]"
