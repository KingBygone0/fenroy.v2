# Fenroy Admin Panel Setup

Run these commands in order from the project root (C:\Users\King\Documents\LYNJAY\laravel\fenroy):

## 1. Install Filament
```
composer require filament/filament:"^3.2" --with-all-dependencies
```

## 2. Install the admin panel
```
php artisan filament:install --panels
```
When prompted for panel ID, enter: admin

## 3. Create your admin user
```
php artisan make:filament-user
```
Enter name, email and password when prompted.

## 4. Run database migrations
```
php artisan migrate
```

## 5. Access the admin panel
Open: http://127.0.0.1:8000/admin

The admin panel includes:
- Store Overview Stats widget (sales, orders, products, customers)
- Order management with status/payment filters
- Product management with category, pricing, stock, toggles
