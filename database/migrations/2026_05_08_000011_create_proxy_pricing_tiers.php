<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable("proxy_pricing_tiers")) return;
        Schema::create("proxy_pricing_tiers", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("proxy_type_id");
            $table->unsignedInteger("duration_days")->default(30);
            $table->unsignedInteger("min_quantity")->default(1);
            $table->unsignedInteger("max_quantity")->default(9);
            $table->decimal("price_per_unit", 10, 2);
            $table->boolean("is_active")->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(["proxy_type_id","duration_days","min_quantity"]);
        });
    }
    public function down(): void { Schema::dropIfExists("proxy_pricing_tiers"); }
};
