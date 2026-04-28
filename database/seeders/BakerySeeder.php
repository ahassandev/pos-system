<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\Customer;

class BakerySeeder extends Seeder
{
    public function run()
    {
        // 1. Categories
        $cats = [
            'Cakes' => ['Chocolate Fudge', 'Red Velvet', 'Vanilla Sponge', 'Black Forest'],
            'Pastries' => ['Croissant', 'Chocolate Eclair', 'Fruit Tart', 'Apple Turnover'],
            'Bread' => ['Baguette', 'Sourdough', 'Whole Wheat', 'Milk Bread'],
            'Cookies' => ['Chocolate Chip', 'Oatmeal Raisin', 'Almond Biscotti', 'Macarons'],
            'Beverages' => ['Cappuccino', 'Latte', 'Iced Tea', 'Hot Chocolate']
        ];

        foreach ($cats as $catName => $products) {
            $category = Category::create(['name' => $catName]);
            
            foreach ($products as $pName) {
                Product::create([
                    'category_id' => $category->id,
                    'name' => $pName,
                    'price' => rand(2, 50),
                    'stock' => rand(20, 100),
                    'barcode' => 'BK-' . rand(1000, 9999)
                ]);
            }
        }

        // 2. Customers
        Customer::create(['name' => 'Ahmad Khan', 'phone' => '03001234567']);
        Customer::create(['name' => 'Sara Ali', 'phone' => '03217654321']);
        Customer::create(['name' => 'Zainab Bibi', 'phone' => '03459998887']);
    }
}
