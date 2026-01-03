<?php

namespace Modules\User\Database\seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
// use Modules\User\Models\User;

class UserSeeder extends Seeder
{

    public function run()
    {
        DB::table('options')->insert([
            [
                'title' => 'ادمین کل',
                'kind' => 'job',
                'option' => json_encode(["form" => null, "permissions" => ["*"]]),
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'کاربر عادی',
                'kind' => 'job',
                'option' => json_encode(["form" => null, "permissions" => []]),
                'status' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'خریدار نوع 1',
                'kind' => 'Category',
                'option' => json_encode(["form" => null]),
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'اشتراک طلایی',
                'kind' => 'Plan',
                'option' => json_encode(["form" => null]),
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'اشتراک نقره ای',
                'kind' => 'Plan',
                'option' => json_encode(["form" => null]),
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'اشتراک برنزی',
                'kind' => 'Plan',
                'option' => json_encode(["form" => null]),
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('users')->insert([
            [
                'name' => 'Admin',
                'lastname' => 'User',
                // 'alias' => 'کاربر تست',
                'username' => 'admin',
                'password' => Hash::make('0012300123'),
                'mobile' => "09120703611",
                'sex' => 1,
                'job' => 1,
                'per' => json_encode(["*"]),
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);



        /* end seed */
    }
}
