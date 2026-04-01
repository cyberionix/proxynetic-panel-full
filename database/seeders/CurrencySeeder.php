<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Currency::create(['name' => 'Türk Lirası', 'code' => 'TRY', 'symbol' => '₺', 'symbol_placement' => 'left']);
        Currency::create(['name' => 'ABD Doları', 'code' => 'USD', 'symbol' => '$', 'symbol_placement' => 'left']);
    }
}
