<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\User\Models\ExtData;
use Modules\User\Models\Option;
use Modules\User\Models\User;

class UserSeeder extends Seeder
{

    public function run(): void
    {
        DB::transaction(function () {
            // پایه‌ی گزینه‌ها (مشاغل، دسته، پلن‌ها)
            $adminJob = Option::updateOrCreate(
                ['title' => 'ادمین کل', 'f_id' => null, 'kind' => 'job'],
                ['option' => ['form' => null, 'permissions' => ['*']], 'status' => 1]
            );

            $userJob = Option::updateOrCreate(
                ['title' => 'کاربر عادی', 'f_id' => null, 'kind' => 'job'],
                ['option' => ['form' => null, 'permissions' => []], 'status' => 0]
            );

            $defaultCategory = Option::updateOrCreate(
                ['title' => 'مشتری عادی', 'f_id' => null, 'kind' => 'Category'],
                ['option' => ['form' => null], 'status' => 1]
            );

            $goldPlan = Option::updateOrCreate(
                ['title' => 'اشتراک طلایی', 'f_id' => null, 'kind' => 'Plan'],
                ['option' => ['form' => null], 'status' => 1]
            );
            $silverPlan = Option::updateOrCreate(
                ['title' => 'اشتراک نقره ای', 'f_id' => null, 'kind' => 'Plan'],
                ['option' => ['form' => null], 'status' => 1]
            );
            $bronzePlan = Option::updateOrCreate(
                ['title' => 'اشتراک برنزی', 'f_id' => null, 'kind' => 'Plan'],
                ['option' => ['form' => null], 'status' => 1]
            );

            // کاربران پایه
            $admin = User::updateOrCreate(
                ['username' => 'admin'],
                [
                    'name'           => 'Admin',
                    'lastname'       => 'User',
                    'password'       => Hash::make('0012300123'),
                    'mobile'         => 9120703611,
                    'sex'            => 1,
                    'job'            => $adminJob->id,
                    'per'            => ['*'],
                    'status'         => 1,
                    'is_accountable' => 0,
                ]
            );

            $customer = User::updateOrCreate(
                ['username' => 'customer'],
                [
                    'name'           => 'مشتری عمومی',
                    'lastname'       => '',
                    'password'       => Hash::make('0012300123'),
                    'mobile'         => 9123456789,
                    'sex'            => 1,
                    'job'            => $userJob->id,
                    'per'            => [],
                    'status'         => 1,
                    'is_accountable' => 1,
                ]
            );

            // اتصال کاربر به دسته و پلن پیش‌فرض
            ExtData::firstOrCreate(
                [
                    'f_id' => $customer->id,
                    'm_id' => $defaultCategory->id,
                    'kind' => 'UserCategory',
                ],
                [
                    'status' => 1,
                ]
            );

            ExtData::firstOrCreate(
                [
                    'f_id' => $customer->id,
                    'm_id' => $goldPlan->id,
                    'kind' => 'UserPlan',
                ],
                [
                    'status' => 1,
                ]
            );
        });
    }
}
