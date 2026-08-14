# Admin Dashboard Completion — Design Spec
**Date:** 2026-08-14
**Status:** Approved

---

## Overview

Complete the Fenroy Filament admin panel by adding 7 feature groups. The store is a Laravel/Filament e-commerce supermarket using Ghanaian Cedis (GH₵). All features extend existing Filament resources or add new ones — no changes to the storefront routing or auth system.

---

## 1. OrderResource Enhancements

### Bulk Status Update
- Add a `BulkAction` to the OrderResource list table
- Admin selects multiple orders → picks a target status from a dropdown → confirms
- Only sensible statuses offered (not backwards transitions)
- Uses the same status pipeline: processing → picking → packed → out-for-delivery → delivered, plus cancel and refunded as direct options

### CSV Export
- Add an `ExportAction` (or custom `Action`) to the OrderResource list header
- Respects active filters (status, payment status, date range)
- Exported columns: order_number, customer_name, customer_email, customer_phone, status, payment_status, total, delivery_fee, discount, delivery_address, notes, created_at
- Format: CSV, filename `orders-YYYY-MM-DD.csv`

### Cancel / Refunded Bulk Actions
- Add dedicated bulk actions: "Cancel Selected" and "Mark as Refunded"
- Both require confirmation modal before applying
- These complement the existing per-row Advance Status action

---

## 2. Coupon Analytics (CouponResource Enhancement)

No new page or resource needed.

- Add **Times Used** column: reads existing `used_count` field on the Coupon model
- Add **Total Discount Given** column: computed via a `withSum` or subquery — sum of `discount` field on paid orders that have `coupon_code = coupons.code`
- Both columns are read-only, sortable
- This requires a `discount` column to exist on the `orders` table (already present per audit)

---

## 3. Customer Detail View (UserResource Enhancement)

- Add a `ViewAction` and `Pages\ViewUser` page to UserResource (currently only Edit exists)
- View page sections:
  - **Profile:** name, email, phone, is_admin, created_at
  - **Order History table:** order_number (copyable), status badge, payment_status badge, total (GH₵), created_at — sorted newest first, paginated 10 per page
  - **Summary stats:** Total Orders (count), Total Spent (GH₵ sum of paid orders)
- No editing on this page — Edit page remains separate

---

## 4. Product Performance Widget

- New Filament widget: `ProductPerformanceTable`
- Position: sort order 5, full width, on the main dashboard
- Data: top 10 products by units sold in the last 30 days
- Source: aggregate `order_items` JSON or a dedicated order items relation (depending on how items are stored — if JSON, parse via a raw query or accessor)
- Columns: Rank, Product Name, Category, Units Sold, Revenue (GH₵)
- Only counts paid orders
- Shown only when at least one paid order exists in the last 30 days

---

## 5. Settings Page

### Storage
- New `settings` database table: `id`, `key` (string, unique), `value` (text, nullable), `timestamps`
- New `Setting` Eloquent model with:
  - `static get(string $key, mixed $default = null): mixed`
  - `static set(string $key, mixed $value): void`
- Seeder: seeds all keys with sensible defaults on first deploy
- Model cached per request (single query, all keys loaded at once)

### Filament Page
- New `app/Filament/Pages/StoreSettings.php` page
- Navigation: Administration group, icon: heroicon-o-cog-6-tooth, sort #2
- Sections (using Filament form with `Section` components):
  - **Store Identity:** store_name, store_tagline
  - **Contact:** contact_email, contact_phone
  - **Social Links:** instagram_url, facebook_url, whatsapp_number
  - **Announcement Banner:** banner_enabled (toggle), banner_message (textarea)
  - **Order Rules:** minimum_order_amount (numeric, GH₵)
- Save button updates all keys via `Setting::set()`
- Form loads current values on mount

### Storefront Integration
- Announcement banner displayed in `resources/views/layouts/storefront.blade.php` above both headers when `banner_enabled = true`
- Reads via `Setting::get('banner_enabled')` — single cached query

---

## 6. Wishlist & Address Admin Views

### WishlistResource (read-only)
- Table columns: User (name + email), Product (name), Created At
- Filters: filter by user (select)
- No create/edit/delete actions (customers manage their own)
- Navigation: Commerce group, sort #8, icon: heroicon-o-heart

### AddressResource (read-only)
- Table columns: User (name), Label, Street, City, Region, Is Default badge
- Filters: filter by user
- No create/edit/delete actions
- Navigation: Commerce group, sort #9, icon: heroicon-o-map-pin

---

## 7. Product Bulk Import

### Package
- Use `maatwebsite/excel` (standard Laravel Excel package) for both CSV and Excel (.xlsx) parsing

### Filament Page
- New `app/Filament/Pages/ImportProducts.php`
- Navigation: Commerce group (next to Products), icon: heroicon-o-arrow-up-tray, sort #4 (between Products and Coupons), label: "Import Products"
- Route: `/admin/import-products`

### Workflow
1. Admin uploads CSV or Excel file
2. System parses and shows a **preview table** (first 5 rows) with column mapping confirmation
3. Admin clicks **Import** → rows processed synchronously
4. Results shown: X created, Y updated, Z errors
5. Error rows shown in a table (row number + reason)

### Expected File Columns
| Column | Required | Notes |
|---|---|---|
| name | yes | |
| sku | yes | Used to match existing products for update |
| unit | yes | e.g. "500g pack" |
| type | no | defaults to "grocery" |
| description | no | |
| category | yes | Must match an existing category name (case-insensitive) |
| price | yes | Numeric |
| old_price | no | Numeric |
| stock | yes | Integer |
| is_active | no | 1/0 or true/false, defaults to 1 |
| is_featured | no | 1/0 or true/false, defaults to 0 |
| is_best_seller | no | 1/0 or true/false, defaults to 0 |

### Template Download
- A pre-built `products-import-template.csv` stored in `public/templates/`
- Download link shown prominently on the import page

### Error Handling
- Missing required fields → row skipped, error logged with row number
- Unknown category name → row skipped, error shown
- Duplicate SKU → update existing product (upsert behavior)
- Invalid price/stock (non-numeric) → row skipped, error shown
- Max file size: 5MB

---

## Implementation Order

Build in this order to avoid dependency issues:

1. Settings table + model + seeder (other features may read settings)
2. OrderResource enhancements (bulk actions, CSV export)
3. Coupon analytics columns
4. Customer detail view (UserResource View page)
5. Product performance widget
6. StoreSettings Filament page + storefront banner integration
7. WishlistResource + AddressResource
8. Product bulk import page

---

## Files Touched / Created

**New files:**
- `database/migrations/xxxx_create_settings_table.php`
- `database/seeders/SettingsSeeder.php`
- `app/Models/Setting.php`
- `app/Filament/Pages/StoreSettings.php`
- `app/Filament/Pages/ImportProducts.php`
- `app/Filament/Resources/WishlistResource.php`
- `app/Filament/Resources/AddressResource.php`
- `app/Filament/Widgets/ProductPerformanceTable.php`
- `app/Imports/ProductsImport.php`
- `public/templates/products-import-template.csv`

**Modified files:**
- `app/Filament/Resources/OrderResource.php` — bulk actions, CSV export
- `app/Filament/Resources/CouponResource.php` — analytics columns
- `app/Filament/Resources/UserResource.php` — View page + order history
- `resources/views/layouts/storefront.blade.php` — announcement banner
- `database/seeders/DatabaseSeeder.php` — call SettingsSeeder
- `composer.json` — add maatwebsite/excel

---

## Out of Scope

- Storefront return request form (admin handles manually via order status)
- Role-based permissions / granular admin access
- Email/notification logs
- Queue-based background import (synchronous is sufficient for <500 products)
- Payment gateway settings (Paystack keys stay in .env)
