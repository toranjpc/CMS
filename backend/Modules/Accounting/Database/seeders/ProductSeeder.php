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

        // ساختار سه سطحی دسته‌بندی‌ها ایجاد می‌شود

        $brands = [
            'Royal Canin',
            'Pedigree',
            'Whiskas',
            'Purina',
        ];

        $units = [
            'کیلو',
            'کیسه',
            'قوطی',
            'بسته',
        ];

        $categoryOptions = [];

        // ایجاد سطح اول: نوع حیوان
        $dogCategory = ProductOption::create([
            'title' => 'سگ',
            'f_id' => null, // ریشه
            'kind' => 'category',
            'status' => 1,
        ]);
        $categoryOptions['سگ'] = $dogCategory;

        $catCategory = ProductOption::create([
            'title' => 'گربه',
            'f_id' => null, // ریشه
            'kind' => 'category',
            'status' => 1,
        ]);
        $categoryOptions['گربه'] = $catCategory;

        $birdCategory = ProductOption::create([
            'title' => 'پرنده',
            'f_id' => null, // ریشه
            'kind' => 'category',
            'status' => 1,
        ]);
        $categoryOptions['پرنده'] = $birdCategory;

        // سطح دوم: انواع غذا برای سگ
        $dogDryFood = ProductOption::create([
            'title' => 'غذای خشک',
            'f_id' => $dogCategory->id,
            'kind' => 'category',
            'status' => 1,
        ]);
        $categoryOptions['غذای خشک سگ'] = $dogDryFood;

        $dogWetFood = ProductOption::create([
            'title' => 'غذای تر',
            'f_id' => $dogCategory->id,
            'kind' => 'category',
            'status' => 1,
        ]);
        $categoryOptions['غذای تر سگ'] = $dogWetFood;

        $dogCanFood = ProductOption::create([
            'title' => 'کنسرو',
            'f_id' => $dogCategory->id,
            'kind' => 'category',
            'status' => 1,
        ]);
        $categoryOptions['کنسرو سگ'] = $dogCanFood;

        // سطح دوم: انواع غذا برای گربه
        $catDryFood = ProductOption::create([
            'title' => 'غذای خشک',
            'f_id' => $catCategory->id,
            'kind' => 'category',
            'status' => 1,
        ]);
        $categoryOptions['غذای خشک گربه'] = $catDryFood;

        $catWetFood = ProductOption::create([
            'title' => 'غذای تر',
            'f_id' => $catCategory->id,
            'kind' => 'category',
            'status' => 1,
        ]);
        $categoryOptions['غذای تر گربه'] = $catWetFood;

        $catCanFood = ProductOption::create([
            'title' => 'کنسرو',
            'f_id' => $catCategory->id,
            'kind' => 'category',
            'status' => 1,
        ]);
        $categoryOptions['کنسرو گربه'] = $catCanFood;

        // سطح سوم: سن حیوانات
        $dogPuppy = ProductOption::create([
            'title' => 'بچه',
            'f_id' => $dogDryFood->id,
            'kind' => 'category',
            'status' => 1,
        ]);
        $categoryOptions['بچه سگ'] = $dogPuppy;

        $dogAdult = ProductOption::create([
            'title' => 'بزرگسال',
            'f_id' => $dogDryFood->id,
            'kind' => 'category',
            'status' => 1,
        ]);
        $categoryOptions['بزرگسال سگ'] = $dogAdult;

        $dogSenior = ProductOption::create([
            'title' => 'کهنه‌کار',
            'f_id' => $dogDryFood->id,
            'kind' => 'category',
            'status' => 1,
        ]);
        $categoryOptions['کهنه‌کار سگ'] = $dogSenior;

        $catKitten = ProductOption::create([
            'title' => 'بچه',
            'f_id' => $catWetFood->id,
            'kind' => 'category',
            'status' => 1,
        ]);
        $categoryOptions['بچه گربه'] = $catKitten;

        $catAdult = ProductOption::create([
            'title' => 'بزرگسال',
            'f_id' => $catDryFood->id,
            'kind' => 'category',
            'status' => 1,
        ]);
        $categoryOptions['بزرگسال گربه'] = $catAdult;

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
                'title' => 'غذای خشک سگ بزرگ‌سال',
                'barcode' => 'DOG001',
                'categories' => ['سگ', 'غذای خشک سگ', 'بزرگسال سگ'],
                'brand' => 'Royal Canin',
                'unit' => 'کیسه',
                'variants' => [
                    [
                        'title' => '۱۰ کیلویی',
                        'price' => 2500000,
                        'stock' => 15,
                        'convertUnit' => true,
                        'UnitNumber' => 10,
                        'convertUnitTitle' => 'کیلو'
                    ],
                    [
                        'title' => '۲۰ کیلویی',
                        'price' => 4800000,
                        'stock' => 8,
                        'convertUnit' => true,
                        'UnitNumber' => 20,
                        'convertUnitTitle' => 'کیلو'
                    ],
                ],
            ],
            [
                'title' => 'کنسرو گوشت گوساله برای سگ',
                'barcode' => 'DOG002',
                'categories' => ['سگ', 'کنسرو سگ'],
                'brand' => 'Pedigree',
                'unit' => 'قوطی',
                'variants' => [
                    [
                        'title' => '۴۰۰ گرمی',
                        'price' => 85000,
                        'stock' => 25,
                        'convertUnit' => false,
                        'UnitNumber' => 0,
                        'convertUnitTitle' => null
                    ],
                    [
                        'title' => '۸۰۰ گرمی',
                        'price' => 160000,
                        'stock' => 12,
                        'convertUnit' => false,
                        'UnitNumber' => 0,
                        'convertUnitTitle' => null
                    ],
                ],
            ],
            [
                'title' => 'غذای مرطوب گربه بچه',
                'barcode' => 'CAT001',
                'categories' => ['گربه', 'غذای تر گربه', 'بچه گربه'],
                'brand' => 'Whiskas',
                'unit' => 'کیسه',
                'variants' => [
                    [
                        'title' => '۱۰۰ گرمی × ۵ بسته',
                        'price' => 125000,
                        'stock' => 30,
                        'convertUnit' => true,
                        'UnitNumber' => 5,
                        'convertUnitTitle' => 'بسته'
                    ],
                ],
            ],
            [
                'title' => 'غذای خشک گربه بزرگ‌سال',
                'barcode' => 'CAT002',
                'categories' => ['گربه', 'غذای خشک گربه', 'بزرگسال گربه'],
                'brand' => 'Purina',
                'unit' => 'کیسه',
                'variants' => [
                    [
                        'title' => '۵ کیلویی',
                        'price' => 1200000,
                        'stock' => 20,
                        'convertUnit' => true,
                        'UnitNumber' => 5,
                        'convertUnitTitle' => 'کیلو'
                    ],
                ],
            ],
            [
                'title' => 'غذای خشک سگ بچه',
                'barcode' => 'DOG003',
                'categories' => ['سگ', 'غذای خشک سگ', 'بچه سگ'],
                'brand' => 'Royal Canin',
                'unit' => 'کیسه',
                'variants' => [
                    [
                        'title' => '۲ کیلویی',
                        'price' => 550000,
                        'stock' => 25,
                        'convertUnit' => true,
                        'UnitNumber' => 2,
                        'convertUnitTitle' => 'کیلو'
                    ],
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
            | Attach Categories (Three Levels)
            |--------------------------------------------------------------------------
            */
            if (isset($p['categories']) && is_array($p['categories'])) {
                foreach ($p['categories'] as $categoryName) {
                    if (isset($categoryOptions[$categoryName])) {
                        ExtData::create([
                            'f_id' => $product->id,
                            'm_id' => $categoryOptions[$categoryName]->id,
                            'kind' => 'ProductCategory',
                            'status' => 1,
                        ]);
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Attach Brand
            |--------------------------------------------------------------------------
            */
            ExtData::create([
                'f_id' => $product->id,
                'm_id' => $brandOptions[$p['brand']]->id,
                'kind' => 'ProductBrand',
                'status' => 1,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Attach Unit
            |--------------------------------------------------------------------------
            */
            if (isset($p['unit']) && isset($unitOptions[$p['unit']])) {
                ExtData::create([
                    'f_id' => $product->id,
                    'm_id' => $unitOptions[$p['unit']]->id,
                    'kind' => 'ProductUnit',
                    'status' => 1,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Variants (Product Items)
            |--------------------------------------------------------------------------
            */
            foreach ($p['variants'] as $v) {
                $convertUnitId = null;
                if ($v['convertUnit'] && isset($v['convertUnitTitle']) && isset($unitOptions[$v['convertUnitTitle']])) {
                    $convertUnitId = $unitOptions[$v['convertUnitTitle']]->id;
                }

                ProductItem::create([
                    'user_id'           => $user?->id,
                    'f_id'              => $product->id,
                    'title'             => $v['title'],
                    'firstWarehouse'    => $v['stock'],
                    'current_stock'     => $v['stock'],
                    'firstPrice'        => $v['price'],
                    'sell_price'        => $v['price'],
                    'status'            => 1,
                    'convertUnit'       => $v['convertUnit'] ?? false,
                    'UnitNumber'        => $v['UnitNumber'] ?? 0,
                    'selectConvertUnit' => $convertUnitId,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Bank (صندوق)
        |--------------------------------------------------------------------------
        */
        ProductOption::create([
            'title' => 'صندوق',
            'f_id' => null,
            'kind' => 'bank',
            'option' => [
                'sheba' => null,
                'account_number' => null,
                'card_number' => null,
                'has_pos' => false,
            ],
            'des' => 0, // موجودی اولیه
            'status' => 1,
        ]);
    }
}
