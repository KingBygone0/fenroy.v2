# Task 3 Brief: Coupon Analytics Columns

## Context
Task 3 of 8. Laravel 13 / Filament v5 at C:\Users\King\Documents\LYNJAY\laravel\fenroy.
One file to modify: `app/Filament/Resources/CouponResource.php`

## What to do

1. Read the existing `app/Filament/Resources/CouponResource.php`
2. Add `use Illuminate\Support\Facades\DB;` to the imports
3. In the `table()` method, after the existing `TextColumn::make('used_count')` column, add this new column:

```php
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
```

The `used_count` column already exists — add the new column AFTER it, not replacing it.

## Verify
Run: `php artisan config:clear` (no syntax errors if this passes)
Run: `php -l app/Filament/Resources/CouponResource.php` to check PHP syntax.

## Commit
```bash
git add app/Filament/Resources/CouponResource.php
git commit -m "feat: add total discount given column to coupon list"
```

## Report
Write report to: `.superpowers/sdd/2026-08-14-admin-dashboard-completion/task-3-report.md`
Return: "Status: DONE, commit: [hash]" or "Status: BLOCKED, reason: [reason]"
