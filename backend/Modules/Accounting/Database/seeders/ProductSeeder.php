<?php

namespace Modules\Accounting\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Accounting\Models\Product;
use Modules\Accounting\Models\ProductItem;
use Modules\Accounting\Models\ProductOption;
use Modules\User\Models\ExtData;
use Modules\User\Models\User;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first(); // یا factory

        /*
        |--------------------------------------------------------------------------
        | Product Options (Base)
        |--------------------------------------------------------------------------
        */

        $categories = [
            'موبایل',
            'لپ‌تاپ',
            'لوازم جانبی',
        ];

        $brands = [
            'Apple',
            'Samsung',
            'Asus',
        ];

        $units = [
            'عدد',
            'کارتن',
        ];

        $categoryOptions = [];
        foreach ($categories as $title) {
            $categoryOptions[$title] = ProductOption::create([
                'title' => $title,
                'kind'  => 'category',
                'status' => 1,
            ]);
        }

        $brandOptions = [];
        foreach ($brands as $title) {
            $brandOptions[$title] = ProductOption::create([
                'title' => $title,
                'kind'  => 'brand',
                'status' => 1,
            ]);
        }

        $unitOptions = [];
        foreach ($units as $title) {
            $unitOptions[$title] = ProductOption::create([
                'title' => $title,
                'kind'  => 'unit',
                'status' => 1,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */

        $products = [
            [
                'title' => 'iPhone 15',
                'barcode' => '123',
                'category' => 'موبایل',
                'brand' => 'Apple',
                'variants' => [
                    ['title' => '128GB', 'price' => 85000000, 'stock' => 10],
                    ['title' => '256GB', 'price' => 92000000, 'stock' => 5],
                ],
            ],
            [
                'title' => 'Galaxy S24',
                'barcode' => '456',
                'category' => 'موبایل',
                'brand' => 'Samsung',
                'variants' => [
                    ['title' => '256GB', 'price' => 78000000, 'stock' => 7],
                ],
            ],
            [
                'title' => 'Asus VivoBook',
                'barcode' => '789',
                'category' => 'لپ‌تاپ',
                'brand' => 'Asus',
                'variants' => [
                    ['title' => 'Core i5', 'price' => 54000000, 'stock' => 4],
                    ['title' => 'Core i7', 'price' => 67000000, 'stock' => 2],
                ],
            ],
        ];

        foreach ($products as $p) {

            $product = Product::create([
                'title'         => $p['title'],
                'barcode'       => $p['barcode'],
                'user_id'  => $user?->id,
                'tags'     => $p['title'],
                'des'      => 'محصول آزمایشی ' . $p['title'],
                'album'    => [],
                'form'     => [],
                'tax_rate' => 10,
                'status'   => 1,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Attach Category
            |--------------------------------------------------------------------------
            */
            ExtData::create([
                'f_id' => $product->id,
                'm_id' => $categoryOptions[$p['category']]->id,
                'kind' => 'UserCategory',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Attach Brand
            |--------------------------------------------------------------------------
            */
            ExtData::create([
                'f_id' => $product->id,
                'm_id' => $brandOptions[$p['brand']]->id,
                'kind' => 'UserBrand',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Variants (Product Items)
            |--------------------------------------------------------------------------
            */
            foreach ($p['variants'] as $v) {
                ProductItem::create([
                    'user_id'       => $user?->id,
                    'f_id'          => $product->id,
                    'title'         => $v['title'],
                    'firstWarehouse' => $v['stock'],
                    'current_stock' => $v['stock'],
                    'firstPrice'    => $v['price'],
                    'sell_price'    => $v['price'],
                    'status'        => 1,
                ]);
            }
        }
    }
}
