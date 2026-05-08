<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProxyTypeSetting;
use App\Models\ProxyPricingTier;
use App\Models\ProductCategory;

class ProxyTypeSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            [
                "type_code" => "TRSRO",
                "display_name" => "TRSRO Proxy",
                "max_quantity" => 50,
                "quantity_unit" => "PROXY",
                "category_name" => "TRSRO Proxy",
                "tiers" => [
                    [1, 4, 150.00],
                    [5, 9, 125.00],
                    [10, 24, 120.00],
                    [25, 50, 110.00],
                ],
            ],
            [
                "type_code" => "PRIVATE_SRO",
                "display_name" => "Private SRO Proxy",
                "max_quantity" => 50,
                "category_name" => "Private Proxy",
                "tiers" => [
                    [1, 9, 60.00], [10, 24, 55.00], [25, 49, 50.00], [50, 50, 45.00],
                ],
            ],
            [
                "type_code" => "METIN2",
                "display_name" => "Metin2 Proxy",
                "max_quantity" => 50,
                "category_name" => "Metin2 Proxy",
                "tiers" => [
                    [1, 9, 60.00], [10, 24, 55.00], [25, 49, 50.00], [50, 50, 45.00],
                ],
            ],
            [
                "type_code" => "KNIGHT",
                "display_name" => "Knight Online Proxy",
                "max_quantity" => 50,
                "category_name" => "Knight Online Proxy",
                "tiers" => [
                    [1, 9, 60.00], [10, 24, 55.00], [25, 49, 50.00], [50, 50, 45.00],
                ],
            ],
            [
                "type_code" => "RESIDENTIAL_ROTATING",
                "display_name" => "Rotating Residential Proxy",
                "max_quantity" => 500,
                "quantity_unit" => "GB",
                "category_name" => "Rotating Residential Proxy",
                "tiers" => [
                    [1, 5, 120.00], [6, 10, 110.00], [11, 20, 100.00],
                    [21, 50, 90.00], [51, 100, 80.00],
                    [101, 250, 70.00], [251, 500, 60.00],
                ],
            ],
            [
                "type_code" => "IPV6",
                "display_name" => "IPv6 Proxy",
                "max_quantity" => 50,
                "category_name" => "IPv6 Proxy",
                "tiers" => [
                    [1, 5, 6.00], [6, 10, 5.70], [11, 25, 5.50], [26, 50, 4.80],
                ],
            ],
            [
                "type_code" => "ISP",
                "display_name" => "ISP Proxy",
                "max_quantity" => 100,
                "category_name" => "IPv4 Residential Proxy",
                "tiers" => [
                    [1, 9, 60.00], [10, 24, 55.00], [25, 49, 50.00], [50, 100, 45.00],
                ],
            ],
        ];

        foreach ($defaults as $row) {
            $cat = ProductCategory::where("name", $row["category_name"])->first();
            $setting = ProxyTypeSetting::firstOrCreate(
                ["type_code" => $row["type_code"]],
                [
                    "display_name" => $row["display_name"],
                    "max_quantity" => $row["max_quantity"],
                    "quantity_unit" => $row["quantity_unit"] ?? "PROXY",
                    "delivery_type" => "LOCALTONET",
                    "delivery_items_template" => ["token_pool_id" => "1", "bandwidth_limit" => ["data_size" => "0", "data_size_type" => "4"]],
                    "category_id" => $cat?->id,
                    "default_properties" => null,
                    "is_active" => true,
                ]
            );
            // Default duration = 30 days
            foreach ($row["tiers"] as [$min, $max, $price]) {
                ProxyPricingTier::firstOrCreate(
                    [
                        "proxy_type_id" => $setting->id,
                        "duration_days" => 30,
                        "min_quantity" => $min,
                    ],
                    [
                        "max_quantity" => $max,
                        "price_per_unit" => $price,
                        "is_active" => true,
                    ]
                );
            }
            echo "Seeded: ".$row["type_code"]." (id=".$setting->id.", tiers=".count($row["tiers"]).")\n";
        }
    }
}
