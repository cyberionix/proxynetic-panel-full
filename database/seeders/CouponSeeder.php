<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('coupon_codes')->insert([
            [
                'coupon_code' => 'WELCOME10',
                'only_new_users' => 1,
                'type' => 'PERCENT',
                'use_limit' => 100,
                'end_date' => '2024-12-31',
                'is_active' => 1,
            ],
            [
                'coupon_code' => 'SUMMER2024',
                'only_new_users' => 0,
                'type' => 'FIXED',
                'use_limit' => 50,
                'end_date' => '2024-06-30',
                'is_active' => 1,
            ],
            [
                'coupon_code' => 'FREESHIP',
                'only_new_users' => 0,
                'type' => 'FIXED',
                'use_limit' => null,
                'end_date' => '2024-01-31',
                'is_active' => 1,
            ],
            [
                'coupon_code' => 'SPRING15',
                'only_new_users' => 1,
                'type' => 'PERCENT',
                'use_limit' => 200,
                'end_date' => '2024-05-15',
                'is_active' => 0, // Pasif kupon
            ],
        ]);
    }

}
