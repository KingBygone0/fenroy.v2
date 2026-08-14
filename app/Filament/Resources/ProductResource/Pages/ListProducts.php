<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importCsv')
                ->label('Import CSV')
                ->icon('heroicon-m-arrow-up-tray')
                ->color('gray')
                ->modalDescription('Upload a CSV with columns: name, sku, category, type, unit, price, old_price, stock, description. The category column should be the category slug (e.g. fruits-vegetables).')
                ->form([
                    FileUpload::make('file')
                        ->label('CSV File')
                        ->disk('public')
                        ->directory('imports')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'])
                        ->required(),
                ])
                ->action(function (array $data) {
                    $path = Storage::disk('public')->path($data['file']);

                    if (! file_exists($path)) {
                        Notification::make()->title('File not found')->danger()->send();
                        return;
                    }

                    $handle   = fopen($path, 'r');
                    $headers  = array_map('trim', fgetcsv($handle));
                    $imported = 0;
                    $skipped  = 0;

                    while (($row = fgetcsv($handle)) !== false) {
                        if (count($row) < count($headers)) continue;
                        $data = array_combine($headers, $row);

                        if (empty($data['name'] ?? '')) { $skipped++; continue; }

                        $slug = $data['slug'] ?? Str::slug($data['name']);
                        if (empty($slug)) $slug = Str::slug($data['name']);

                        Product::updateOrCreate(['slug' => $slug], array_filter([
                            'name'        => $data['name'] ?? null,
                            'slug'        => $slug,
                            'sku'         => $data['sku'] ?? null,
                            'category'    => $data['category'] ?? null,
                            'type'        => $data['type'] ?? null,
                            'unit'        => $data['unit'] ?? null,
                            'price'       => is_numeric($data['price'] ?? null) ? (float) $data['price'] : null,
                            'old_price'   => is_numeric($data['old_price'] ?? null) ? (float) $data['old_price'] : null,
                            'stock'       => is_numeric($data['stock'] ?? null) ? (int) $data['stock'] : 0,
                            'description' => $data['description'] ?? null,
                            'is_active'   => true,
                        ], fn ($v) => $v !== null && $v !== ''));

                        $imported++;
                    }

                    fclose($handle);
                    Storage::disk('public')->delete($data['file'] ?? '');

                    Notification::make()
                        ->title("Imported {$imported} products" . ($skipped ? ", skipped {$skipped}" : ''))
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
