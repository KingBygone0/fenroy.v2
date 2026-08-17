# Task 4 Report: Customer Detail View (UserResource)

## Status: DONE

## Commit
`4f7650b` — feat: add customer detail view with order history and spending stats

## Files Created/Modified

### Created: `app/Filament/Resources/UserResource/Pages/ViewUser.php`
- Custom Filament Page (not ViewRecord) extending `Page` directly
- `mount()` loads user via `User::findOrFail($record)`
- `getTitle()` returns user name as page heading
- `getHeaderActions()` returns EditAction linked to the loaded record
- `getOrdersHtml()` queries orders by `customer_email`, renders an HTML table with status/payment badge coloring
- `getStats()` uses the clone pattern to avoid the builder-reuse bug: `(clone $baseQuery)->where(...)` for the paid sum

### Created: `resources/views/filament/resources/user-resource/pages/view-user.blade.php`
- Profile card (name, email, phone, joined, admin badge if applicable)
- 2-column stats grid (total orders count, total spent on paid orders)
- Order history table rendered via `{!! $this->getOrdersHtml() !!}`

### Modified: `app/Filament/Resources/UserResource.php`
- Added `use Filament\Actions\ViewAction;`
- Table actions updated to include `ViewAction::make()` before `EditAction::make()`
- `getPages()` now registers the `view` route: `Pages\ViewUser::route('/{record}')`

## Bug Fix Applied
The brief noted that the original plan's `getStats()` had a builder-reuse bug. Applied the `clone` fix as specified:
```php
'total_spent' => (clone $baseQuery)->where('payment_status', 'paid')->sum('total'),
```

## Verification
- `php -l app/Filament/Resources/UserResource.php` — No syntax errors
- `php -l app/Filament/Resources/UserResource/Pages/ViewUser.php` — No syntax errors
