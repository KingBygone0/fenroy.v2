<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\DeliveryZone;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::create([
            'name'     => 'King',
            'email'    => 'king@fenroy.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        $categories = [
            ['name' => 'Fruits & Vegetables', 'slug' => 'fruits-vegetables', 'icon' => '🥦', 'sort_order' => 1],
            ['name' => 'Beverages',            'slug' => 'beverages',         'icon' => '🧃', 'sort_order' => 2],
            ['name' => 'Dairy & Eggs',         'slug' => 'dairy-eggs',        'icon' => '🥚', 'sort_order' => 3],
            ['name' => 'Pantry',               'slug' => 'pantry',            'icon' => '🛒', 'sort_order' => 4],
            ['name' => 'Snacks & Sweets',      'slug' => 'snacks-sweets',     'icon' => '🍫', 'sort_order' => 5],
            ['name' => 'Personal Care',        'slug' => 'personal-care',     'icon' => '🧴', 'sort_order' => 6],
            ['name' => 'Household',            'slug' => 'household',         'icon' => '🧹', 'sort_order' => 7],
            ['name' => 'Baby Care',            'slug' => 'baby-care',         'icon' => '👶', 'sort_order' => 8],
        ];

        foreach ($categories as $cat) {
            Category::create(array_merge($cat, ['is_active' => true]));
        }

        $deliveryZones = [
            ['name' => 'Adum · Nhyiaeso · Central Kumasi', 'areas' => ['Adum', 'Nhyiaeso', 'Asafo', 'Central Market'], 'fee' =>  8.00, 'free_above' => 180.00, 'sort_order' => 1],
            ['name' => 'Bantama · Suame · Asokwa',          'areas' => ['Bantama', 'Suame', 'Asokwa'],                  'fee' => 12.00, 'free_above' => 220.00, 'sort_order' => 2],
            ['name' => 'KNUST · Kotei · Ayigya',            'areas' => ['KNUST', 'Kotei', 'Ayigya', 'Bomso'],           'fee' => 15.00, 'free_above' => 250.00, 'sort_order' => 3],
            ['name' => 'Asokore Mampong · Airport Area',    'areas' => ['Asokore Mampong', 'Kumasi Airport'],           'fee' => 18.00, 'free_above' => 300.00, 'sort_order' => 4],
            ['name' => 'Ejisu · Juaben',                    'areas' => ['Ejisu', 'Juaben'],                             'fee' => 28.00, 'free_above' => 400.00, 'sort_order' => 5],
            ['name' => 'Offinso · Bekwai Road',             'areas' => ['Offinso', 'Bekwai Road', 'Manhyia'],           'fee' => 35.00, 'free_above' => null,   'sort_order' => 6],
        ];

        foreach ($deliveryZones as $zone) {
            DeliveryZone::create(array_merge($zone, ['is_active' => true]));
        }

        $this->call([
            SettingsSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
