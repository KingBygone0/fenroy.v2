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

    // WooCommerce export → our field names
    private array $aliases = [
        'name'          => ['name'],
        'sku'           => ['sku'],
        'price'         => ['price', 'regular_price', 'meta_fenroy_shelf_price_ghs'],
        'stock'         => ['stock'],
        'category'      => ['category', 'categories'],
        'unit'          => ['unit'],
        'description'   => ['description', 'short_description'],
        'type'          => ['type'],
        'old_price'     => ['old_price', 'sale_price'],
        'is_active'     => ['is_active', 'published'],
        'is_featured'   => ['is_featured'],
        'is_best_seller'=> ['is_best_seller'],
    ];

    public function __construct()
    {
        $this->validCategories = Category::pluck('name')
            ->map(fn ($n) => strtolower(trim($n)))
            ->toArray();
    }

    private function get(Collection $row, string $field): mixed
    {
        foreach ($this->aliases[$field] ?? [$field] as $key) {
            $val = $row->get($key);
            if ($val !== null) return $val;
        }
        return null;
    }

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            $this->errors[] = 'File appears empty or could not be read.';
            return;
        }

        $firstRow = $rows->first();

        // Must have at minimum: name, sku, price, stock, category
        $required = ['name', 'sku', 'price', 'stock', 'category'];
        $missing  = array_filter($required, fn ($f) => is_null($this->get($firstRow, $f)));
        if ($missing) {
            $found = $firstRow->keys()->implode(', ');
            $this->errors[] = 'Column mismatch — could not find: ' . implode(', ', $missing)
                . '. Columns in file: ' . ($found ?: '(none)');
            return;
        }

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;

            foreach ($required as $field) {
                $val = $this->get($row, $field);
                if (($val === null || $val === '') && $val !== '0') {
                    $this->errors[] = "Row {$rowNum}: Missing required field '{$field}'";
                    continue 2;
                }
            }

            $categoryRaw = trim((string) $this->get($row, 'category'));
            // WooCommerce may list multiple categories separated by comma — take first
            if (str_contains($categoryRaw, ',')) {
                $categoryRaw = trim(explode(',', $categoryRaw)[0]);
            }

            if (! in_array(strtolower($categoryRaw), $this->validCategories, true)) {
                $this->errors[] = "Row {$rowNum}: Unknown category '{$categoryRaw}'";
                continue;
            }

            // Store as slug to match how CategoryPage queries products
            $category = Str::slug($categoryRaw);

            $price = $this->get($row, 'price');
            if (! is_numeric($price)) {
                $this->errors[] = "Row {$rowNum}: Invalid price '{$price}'";
                continue;
            }

            $stock = $this->get($row, 'stock');
            if (! is_numeric($stock)) {
                $this->errors[] = "Row {$rowNum}: Invalid stock '{$stock}'";
                continue;
            }

            $oldPrice    = $this->get($row, 'old_price');
            $unitRaw     = $this->get($row, 'unit');
            $isActiveRaw = $this->get($row, 'is_active');

            $rawName = strip_tags(trim((string) $this->get($row, 'name')));
            $data = [
                'name'           => $rawName,
                'slug'           => Str::slug($rawName),
                'unit'           => $unitRaw ? strip_tags(trim((string) $unitRaw)) : 'piece',
                'type'           => strip_tags(trim((string) ($this->get($row, 'type') ?? 'grocery')) ?: 'grocery'),
                'description'    => strip_tags(trim((string) ($this->get($row, 'description') ?? ''))),
                'category'       => $category,
                'price'          => (float) $price,
                'old_price'      => ($oldPrice !== null && is_numeric($oldPrice)) ? (float) $oldPrice : null,
                'stock'          => (int) $stock,
                'is_active'      => $isActiveRaw !== null ? (bool)(int) $isActiveRaw : true,
                'is_featured'    => $this->get($row, 'is_featured') !== null ? (bool)(int) $this->get($row, 'is_featured') : false,
                'is_best_seller' => $this->get($row, 'is_best_seller') !== null ? (bool)(int) $this->get($row, 'is_best_seller') : false,
            ];

            $sku      = trim((string) $this->get($row, 'sku'));
            $existing = Product::where('sku', $sku)->first();

            if ($existing) {
                $existing->update($data);
                $this->updated++;
            } else {
                $data['sku'] = $sku;
                Product::create($data);
                $this->created++;
            }
        }
    }
}
