<?php

namespace Database\Seeders;

use App\Models\Basket;
use App\Models\BasketItem;
use App\Models\Currency;
use App\Models\Price;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSubCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $category = ProductCategory::create(["name" => "Metin 2 Proxy"]);

        $product = Product::create(['name' => 'Metin2 Proxy 1 Adet', 'vat_percent' => 20, 'category_id' => $category->id]);
        Price::create([
            "duration" => "1",
            "duration_unit" => "MONTHLY",
            "price" => "40.00",
            "currency_id" => Currency::DEFAULT_ID,
            "product_id" => $product->id
        ]);
        Price::create([
            "duration" => "3",
            "duration_unit" => "MONTHLY",
            "price" => "100.00",
            "currency_id" => Currency::DEFAULT_ID,
            "product_id" => $product->id
        ]);
        Price::create([
            "duration" => "1",
            "duration_unit" => "YEARLY",
            "price" => "400.00",
            "currency_id" => Currency::DEFAULT_ID,
            "product_id" => $product->id
        ]);

        $category = ProductCategory::create(["name" => "4G/5G Rotating Mobil Proxy"]);

        $subCategory = ProductCategory::create(["name" => "Polonya 4G Mobil Proxy", "parent_id" => $category->id]);
        $product = Product::create([
            'name' => 'Polonya 4G Aylık',
            'vat_percent' => 20,
            'properties' => "LTE 4G Bağlantı\r\nTürk Telekom\r\n600 GB Kota",
            'attrs' => [
                [
                    "type" => "radio",
                    "service_type" => "protocol_select",
                    "name" => Str::slug("Protocol Seçimi", "_"),
                    "label" => "Protocol Seçimi<br>HTTP & Socks5",
                    "options" => [
                        [
                            "label" => "HTTP Ücretsiz",
                            "value" => Str::slug("HTTP", "_"),
                            "price" => 0.00,
                        ],
                        [
                            "label" => "Socks5",
                            "value" => Str::slug("socks5", "_"),
                            "price" => 15.00,
                        ]
                    ]
                ],
                [
                    "type" => "select",
                    "service_type" => "quota",
                    "name" => "quota",
                    "label" => "Ek Kota",
                    "options" => [
                        [
                            "label" => "2 GB",
                            "value" => 2,
                            "price" => 100,
                        ],
                        [
                            "label" => "5 GB",
                            "value" => 5,
                            "price" => 300,
                        ]
                    ]
                ]
            ],
            "category_id" => $subCategory->id]);
        Price::create([
            "duration" => "1",
            "duration_unit" => "MONTHLY",
            "price" => "1950.00",
            "currency_id" => Currency::DEFAULT_ID,
            "product_id" => $product->id
        ]);
        ProductCategory::create(["name" => "Amerika 5G Mobil Proxy", "parent_id" => $category->id]);
    }
}
