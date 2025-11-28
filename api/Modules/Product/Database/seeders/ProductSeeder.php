<?php

namespace Modules\Product\Database\seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
// use Modules\Product\Models\Product;

class ProductSeeder extends Seeder
{

    public function run()
    {
        DB::table('productoptions')->insert([
            [
                'title' => 'ادمین کل',
                'kind' => 'ProductCategory',
                'option' => json_encode(["form" => null, "permissions" => null]),
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'کاربر عادی',
                'kind' => 'ProductCategory',
                'option' => json_encode(["form" => null, "permissions" => null]),
                'status' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'خریدار نوع 1',
                'kind' => 'ProductGroup',
                'option' => json_encode(["form" => null]),
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);


        /* end seed */
    }
}
