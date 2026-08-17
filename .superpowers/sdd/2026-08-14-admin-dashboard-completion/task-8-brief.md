# Task 8 Brief: Product Bulk Import

## Context
Task 8 of 8 (final). Laravel 13 / PHP 8.4 / Filament v5 at C:\Users\King\Documents\LYNJAY\laravel\fenroy.

**Key facts:**
- `App\Models\Category` exists — has `name` field
- `App\Models\Product` — upsert key is `sku`; `category` is stored as a plain string (category name, NOT a FK)
- Product fields: `name`, `slug`, `sku`, `unit`, `type`, `description`, `category`, `price`, `old_price`, `stock`, `is_active`, `is_featured`, `is_best_seller`
- Filament auto-discovers pages from `app/Filament/Pages/` — no need to touch AdminPanelProvider
- Import is synchronous — no queues
- Currency: `'GH₵ '` (with trailing space) — not relevant to this task but noted

**Pre-flight rulings applied in this brief:**
1. `goToPreview()` is included in `ImportProducts.php` directly (plan adds it in Step 6, but we implement it all at once)
2. Blade view is written as the final clean version (the plan had a messy Step 5 version that Step 6 replaces — we skip the intermediate and write the final directly)

---

## Step 1: Install maatwebsite/excel

```bash
composer require maatwebsite/excel
```

Expected: package installs with no errors. Laravel auto-discovery handles the service provider.

---

## Step 2: Create the template CSV

Create directory `public/templates/` if needed.

File: `public/templates/products-import-template.csv`

```
name,sku,unit,type,description,category,price,old_price,stock,is_active,is_featured,is_best_seller
Tomatoes (1kg),TOM-001,1kg pack,grocery,Fresh ripe tomatoes,Vegetables,5.99,,50,1,0,0
Chicken Breast,CHK-001,500g pack,grocery,Boneless chicken breast,Meat & Fish,25.00,28.00,30,1,1,0
```

---

## Step 3: Create `app/Imports/ProductsImport.php`

```php
<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToCollection, WithHeadingRow
{
    public int $created  = 0;
    public int $updated  = 0;
    public array $errors = [];

    private array $validCategories = [];

    public function __construct()
    {
        $this->validCategories = Category::pluck('name')
            ->map(fn ($n) => strtolower(trim($n)))
            ->toArray();
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;

            foreach (['name', 'sku', 'unit', 'price', 'stock', 'category'] as $field) {
                if (empty($row[$field]) && $row[$field] !== '0') {
                    $this->errors[] = "Row {$rowNum}: Missing required field '{$field}'";
                    continue 2;
                }
            }

            if (! in_array(strtolower(trim($row['category'])), $this->validCategories, true)) {
                $this->errors[] = "Row {$rowNum}: Unknown category '{$row['category']}'";
                continue;
            }

            if (! is_numeric($row['price'])) {
                $this->errors[] = "Row {$rowNum}: Invalid price '{$row['price']}'";
                continue;
            }

            if (! is_numeric($row['stock'])) {
                $this->errors[] = "Row {$rowNum}: Invalid stock '{$row['stock']}'";
                continue;
            }

            $data = [
                'name'           => trim($row['name']),
                'slug'           => Str::slug(trim($row['name'])),
                'unit'           => trim($row['unit']),
                'type'           => trim($row['type'] ?? 'grocery') ?: 'grocery',
                'description'    => trim($row['description'] ?? ''),
                'category'       => trim($row['category']),
                'price'          => (float) $row['price'],
                'old_price'      => (isset($row['old_price']) && is_numeric($row['old_price'])) ? (float) $row['old_price'] : null,
                'stock'          => (int) $row['stock'],
                'is_active'      => isset($row['is_active']) ? (bool)(int)$row['is_active'] : true,
                'is_featured'    => isset($row['is_featured']) ? (bool)(int)$row['is_featured'] : false,
                'is_best_seller' => isset($row['is_best_seller']) ? (bool)(int)$row['is_best_seller'] : false,
            ];

            $existing = Product::where('sku', trim($row['sku']))->first();

            if ($existing) {
                $existing->update($data);
                $this->updated++;
            } else {
                $data['sku'] = trim($row['sku']);
                Product::create($data);
                $this->created++;
            }
        }
    }
}
```

---

## Step 4: Create `app/Filament/Pages/ImportProducts.php`

**IMPORTANT:** Include `goToPreview()` in this file directly (it is used by the Blade view's Alpine.js upload handler).

```php
<?php

namespace App\Filament\Pages;

use App\Imports\ProductsImport;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Maatwebsite\Excel\Facades\Excel;

class ImportProducts extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static string|\UnitEnum|null $navigationGroup = 'Commerce';

    protected static ?string $navigationLabel = 'Import Products';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.import-products';

    public string $stage = 'upload'; // 'upload' | 'preview' | 'done'

    public ?string $tempPath = null;

    public array $previewRows = [];

    public array $previewHeaders = [];

    public int $totalRows = 0;

    public int $created = 0;

    public int $updated = 0;

    public array $importErrors = [];

    public function goToPreview(): void
    {
        if (! $this->tempPath || ! file_exists($this->tempPath)) {
            Notification::make()->title('File not found. Please re-upload.')->danger()->send();
            return;
        }

        $handle = fopen($this->tempPath, 'r');
        $this->previewHeaders = fgetcsv($handle) ?: [];
        $previewData = [];
        $count = 0;
        while (($row = fgetcsv($handle)) !== false && $count < 5) {
            $previewData[] = $row;
            $count++;
        }
        fclose($handle);
        $this->previewRows = $previewData;

        $this->stage = 'preview';
    }

    public function runImport(): void
    {
        if (! $this->tempPath || ! file_exists($this->tempPath)) {
            Notification::make()->title('Import file not found. Please re-upload.')->danger()->send();
            $this->stage = 'upload';
            return;
        }

        $import = new ProductsImport();

        $ext = strtolower(pathinfo($this->tempPath, PATHINFO_EXTENSION));
        $type = $ext === 'xlsx' ? \Maatwebsite\Excel\Excel::XLSX : \Maatwebsite\Excel\Excel::CSV;

        Excel::import($import, $this->tempPath, null, $type);

        $this->created      = $import->created;
        $this->updated      = $import->updated;
        $this->importErrors = $import->errors;

        @unlink($this->tempPath);
        $this->tempPath = null;

        $this->stage = 'done';

        Notification::make()
            ->title("Import complete: {$this->created} created, {$this->updated} updated" . (count($this->importErrors) ? ', ' . count($this->importErrors) . ' errors' : ''))
            ->success()
            ->send();
    }

    public function resetImport(): void
    {
        $this->stage          = 'upload';
        $this->tempPath       = null;
        $this->previewRows    = [];
        $this->previewHeaders = [];
        $this->totalRows      = 0;
        $this->created        = 0;
        $this->updated        = 0;
        $this->importErrors   = [];
    }
}
```

---

## Step 5: Create `resources/views/filament/pages/import-products.blade.php`

Write the FINAL version directly (clean, single file input, Alpine.js fetch to upload route):

```blade
<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Template download --}}
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:16px;display:flex;align-items:center;justify-content:space-between;">
            <div>
                <p style="font-size:14px;font-weight:600;color:#15803d;">Download Template</p>
                <p style="font-size:13px;color:#166534;margin-top:2px;">Use this CSV template to prepare your product data correctly.</p>
            </div>
            <a href="/templates/products-import-template.csv" download
               style="padding:8px 16px;background:#16a34a;color:white;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
                Download Template
            </a>
        </div>

        {{-- STAGE: Upload --}}
        @if($stage === 'upload')
        <div style="background:white;border:1px solid #e5e7eb;border-radius:12px;padding:24px;">
            <h2 style="font-size:15px;font-weight:700;color:#111827;margin-bottom:4px;">Upload Product File</h2>
            <p style="font-size:13px;color:#6b7280;margin-bottom:20px;">Accepted: <strong>CSV</strong> or <strong>Excel (.xlsx)</strong>. Max 5MB. Existing products matched by SKU will be updated.</p>

            <div x-data="{ uploading: false, selectedName: '' }">
                <input type="file" accept=".csv,.xlsx"
                    style="display:block;width:100%;padding:10px;border:2px dashed #d1d5db;border-radius:8px;font-size:14px;cursor:pointer;background:#fafafa;"
                    x-on:change="
                        const file = $event.target.files[0];
                        if (!file) return;
                        selectedName = file.name;
                        uploading = true;
                        const reader = new FileReader();
                        reader.onload = async (e) => {
                            const base64 = e.target.result.split(',')[1];
                            const ext = file.name.split('.').pop().toLowerCase();
                            const resp = await fetch('/admin/import-products/upload', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                },
                                body: JSON.stringify({ file: base64, name: file.name, ext })
                            });
                            const data = await resp.json();
                            uploading = false;
                            if (data.path) {
                                await $wire.set('tempPath', data.path);
                                await $wire.call('goToPreview');
                            }
                        };
                        reader.readAsDataURL(file);
                    ">
                <p x-show="uploading" x-text="'Uploading ' + selectedName + '...'" style="font-size:13px;color:#374151;margin-top:8px;"></p>
            </div>
        </div>
        @endif

        {{-- STAGE: Preview --}}
        @if($stage === 'preview')
        <div style="background:white;border:1px solid #e5e7eb;border-radius:12px;padding:24px;">
            <h2 style="font-size:15px;font-weight:700;color:#111827;margin-bottom:4px;">Preview (first {{ count($previewRows) }} rows)</h2>
            <p style="font-size:13px;color:#6b7280;margin-bottom:16px;">Review the data below, then click Import to process the file.</p>

            @if(count($previewRows) > 0)
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;font-size:13px;">
                    <thead>
                        <tr style="background:#f9fafb;">
                            @foreach($previewHeaders as $header)
                            <th style="padding:8px 12px;text-align:left;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.04em;font-size:11px;white-space:nowrap;">{{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($previewRows as $row)
                        <tr>
                            @foreach($row as $cell)
                            <td style="padding:8px 12px;border-bottom:1px solid #f3f4f6;color:#374151;white-space:nowrap;">{{ $cell }}</td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p style="color:#ef4444;font-size:13px;">No data rows found in the file.</p>
            @endif

            <div style="margin-top:20px;display:flex;gap:12px;">
                <button wire:click="runImport" style="padding:10px 20px;background:#E53935;color:white;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
                    Confirm Import
                </button>
                <button wire:click="resetImport" style="padding:10px 20px;background:#f3f4f6;color:#374151;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
                    Cancel
                </button>
            </div>
        </div>
        @endif

        {{-- STAGE: Done --}}
        @if($stage === 'done')
        <div style="background:white;border:1px solid #e5e7eb;border-radius:12px;padding:24px;">
            <h2 style="font-size:15px;font-weight:700;color:#111827;margin-bottom:16px;">Import Complete</h2>

            <div style="display:flex;gap:16px;margin-bottom:20px;">
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:16px 24px;text-align:center;">
                    <p style="font-size:28px;font-weight:800;color:#15803d;">{{ $created }}</p>
                    <p style="font-size:13px;color:#166534;margin-top:4px;">Products Created</p>
                </div>
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:16px 24px;text-align:center;">
                    <p style="font-size:28px;font-weight:800;color:#1d4ed8;">{{ $updated }}</p>
                    <p style="font-size:13px;color:#1e40af;margin-top:4px;">Products Updated</p>
                </div>
                @if(count($importErrors) > 0)
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:16px 24px;text-align:center;">
                    <p style="font-size:28px;font-weight:800;color:#b91c1c;">{{ count($importErrors) }}</p>
                    <p style="font-size:13px;color:#991b1b;margin-top:4px;">Rows Skipped</p>
                </div>
                @endif
            </div>

            @if(count($importErrors) > 0)
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:16px;margin-bottom:20px;">
                <p style="font-size:13px;font-weight:600;color:#b91c1c;margin-bottom:8px;">Skipped rows:</p>
                <ul style="list-style:disc;padding-left:20px;font-size:13px;color:#991b1b;">
                    @foreach($importErrors as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div style="display:flex;gap:12px;">
                <button wire:click="resetImport" style="padding:10px 20px;background:#E53935;color:white;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
                    Import Another File
                </button>
                <a href="/admin/products" style="padding:10px 20px;background:#f3f4f6;color:#374151;border-radius:8px;font-size:14px;font-weight:600;text-decoration:none;">
                    View Products
                </a>
            </div>
        </div>
        @endif

    </div>
</x-filament-panels::page>
```

---

## Step 6: Add upload route to `routes/web.php`

Open `routes/web.php`. It already has `use Illuminate\Support\Facades\Route;` and `use Illuminate\Http\Request;` — check the imports before adding. Add `use Illuminate\Http\Request;` if not present.

Append this route **before** the closing of the file (or near the end, in the admin/auth section if one exists):

```php
Route::post('/admin/import-products/upload', function (Request $request) {
    $request->validate([
        'file' => 'required|string',
        'name' => 'required|string',
        'ext'  => 'required|in:csv,xlsx',
    ]);

    $content  = base64_decode($request->file);
    $filename = 'imports/import-' . now()->format('YmdHis') . '.' . $request->ext;
    $path     = storage_path('app/' . $filename);

    if (! is_dir(dirname($path))) {
        mkdir(dirname($path), 0755, true);
    }

    file_put_contents($path, $content);

    return response()->json(['path' => $path]);
})->middleware(['auth'])->name('admin.import.upload');
```

**Note:** `auth` middleware ensures only logged-in users can upload. The decoded file goes to `storage/app/imports/` (not public).

---

## Verify

```bash
php -l app/Imports/ProductsImport.php
php -l app/Filament/Pages/ImportProducts.php
```

Both should return "No syntax errors detected."

---

## Commit

```bash
git add app/Imports/ProductsImport.php app/Filament/Pages/ImportProducts.php resources/views/filament/pages/import-products.blade.php public/templates/products-import-template.csv routes/web.php
git commit -m "feat: add product bulk import (CSV/Excel) with preview and error reporting"
```

---

## Report

Write to: `.superpowers/sdd/2026-08-14-admin-dashboard-completion/task-8-report.md`

Return (in your final message): "Status: DONE, commit: [hash]" or "Status: BLOCKED, reason: [reason]"

Do NOT dispatch any subagents. Do NOT run a reviewer.
