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
