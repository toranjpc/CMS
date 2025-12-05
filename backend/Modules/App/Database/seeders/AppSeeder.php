<?php

namespace Modules\App\Database\seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
// use Modules\App\Models\App;

class AppSeeder extends Seeder
{

    public function run()
    {
        DB::table('appoptions')->insert([
            [
                'title' => 'عنوان نرم افزار',
                'url' => '/',
                'uid' => 1,
                'sett' => ["icon" => "icon.png", "favicon" => "favicon.png"],
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);


        /* end seed */
    }
}
