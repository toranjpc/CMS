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
        DB::table('useroptions')->insert([
            [
                'title' => 'ادمین کل',
                'kind' => 'UserCategory',
                'option' => json_encode(["form" => null, "permissions" => null]),
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'کاربر عادی',
                'kind' => 'UserCategory',
                'option' => json_encode(["form" => null, "permissions" => null]),
                'status' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'خریدار نوع 1',
                'kind' => 'UserGroup',
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
                'alias' => 'کاربر تست',
                'username' => 'admin',
                'password' => Hash::make('0012300123'),
                'mobile' => "09120703611",
                'email' => 'admin@example.com',
                'sex' => 1,
                'job' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);



        /* end seed */
    }
}
