<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Admin;
use App\Models\Alert;
use App\Models\Price;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserGroup;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Admin::create([
            'first_name' => 'Netpus',
            'last_name' => 'Yazılım',
            'email' => 'admin@netpus.com.tr',
            'password' => Hash::make('123123')
        ]);

        User::create([
            'first_name' => 'Ahmet',
            'last_name' => 'Çakmak',
            'email' => 'ahmet@netpus.com.tr',
            'phone' => '905079747767',
            'password' => Hash::make('123123'),
        ]);
        User::create([
            'first_name' => 'Mert',
            'last_name' => 'Müstehlik',
            'email' => 'mert.mustehlik@netpus.com.tr',
            'password' => Hash::make('123123')
        ]);

        $this->call(CurrencySeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(StaticSeeder::class);


    }
}
